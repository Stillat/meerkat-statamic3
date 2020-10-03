<?php

namespace Stillat\Meerkat\Tags\Responses;

use Carbon\Carbon;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\PagedDataSet;
use Stillat\Meerkat\Core\Data\PredicateBuilder;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Tags\MeerkatTag;
use Stillat\Meerkat\Tags\Output\RecursiveThreadRenderer;

// TODO: Provide extra meta data, such as `comments_enabled` in Antlers contexts.
class CollectionRenderer extends MeerkatTag
{
    const PARAM_UNAPPROVED = 'include_unapproved';
    const PARAM_INCLUDE_SPAM = 'include_spam';
    const PARAM_FILTER = 'filter';
    const PARAM_FLAT = 'flat';
    const PARAM_ORDER = 'order';
    const PARAM_SINCE = 'since';
    const PARAM_UNTIL = 'until';
    const PARAM_COLLECTION_ALIAS = 'as';
    const PARAM_AUTO_MARKDOWN = 'auto_markdown';

    const PARAM_PAGINATE = 'paginate';
    const PARAM_OFFSET = 'offset';
    const PARAM_PAGEBY = 'pageby';
    const PARAM_DEFAULT_PAGEBY = 'page';
    const PARAM_LIMIT = 'limit';

    const RETURN_TOTAL_RESULTS = 'total_results';
    const RETURN_HAS_RESULTS = 'has_results';
    const RETURN_NO_RESULTS = 'no_results';
    const RETURN_ITEMS_COUNT = 'items_count';
    const DEFAULT_COLLECTION_NAME = 'comments';

    public $tagContext = '';
    protected $filterManager = null;
    protected $threadManager = null;
    protected $sanitizer = null;
    protected $paginated = false;
    protected $pageLimit = null;
    protected $pageOffset = 0;
    protected $pageBy = 'page';
    protected $query = null;
    private $threadId = null;
    private $autoMarkdown = false;

    public function __construct(
        ThreadManagerContract $threadManager,
        CommentFilterManager $filterManager,
        SanitationManagerContract $sanitizer,
        DataQuery $query)
    {
        $this->query = $query;
        $this->filterManager = $filterManager;
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Sets the internal thread identifier.
     *
     * @param string $threadId The thread identifier.
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;
    }

    /**
     * Renders the tag content.
     *
     * @return string|array
     * @throws FilterException
     */
    public function render()
    {
        $this->parseParameters();

        $collectionName = $this->getParameterValue(
            CollectionRenderer::PARAM_COLLECTION_ALIAS, CollectionRenderer::DEFAULT_COLLECTION_NAME
        );

        $this->applyFilterRestrictions();

        $runtimeContext = $this->getRuntimeContext();
        $runtimeContext->templateTagContext = $this->tagContext;

        $this->query->withContext($runtimeContext)->withMarkdown($this->autoMarkdown);

        $flatList = $this->getParameterValue(CollectionRenderer::PARAM_FLAT, false);

        $this->applyParamFiltersToQuery();
        $this->applyParamOrdersToQuery();

        $thread = $this->threadManager->findById($this->threadId);

        $comments = $thread->getComments();

        $this->query->limit($this->pageLimit)->skip($this->pageOffset);

        if ($this->paginated) {
            $currentPage = request()->input($this->pageBy, 0);

            $this->query->pageBy($this->pageBy)->forPage($currentPage);
        }

        if ($this->hasParameterValue('group_by_date')) {
            $dateFormat = $this->getParameterValue('group_by_date');

            $this->query->nameAllGroups('date_groups')->groupName('date_group')
                ->collectionName($collectionName)
                ->groupBy('group:date', function (CommentContract $comment) use ($dateFormat) {
                    $comment->setDataAttribute('group:date', $comment->getCommentDate()->format($dateFormat));
                });
        }

        $result = $this->query->getCollection($comments, $collectionName);

        $this->removeFilterRestrictions();

        if ($result instanceof GroupedDataSetContract) {
            if ($result instanceof PagedGroupedDataSetContract) {
                return $this->renderPaginatedGroupedDataset($result);
            }

            return $this->renderGroupedComments($result);
        } elseif ($result instanceof PagedDataSetContract) {
            return $this->renderPaginatedComments($result, $collectionName);
        } elseif ($result instanceof DataSetContract) {
            return $this->renderListComments($result->getData(), $collectionName, $flatList);
        }

        return '';
    }

    /**
     * Parses the tag's current context and sets the internal state.
     */
    private function parseParameters()
    {
        $this->paginated = $this->getParameterValue(self::PARAM_PAGINATE, false);
        $this->pageOffset = $this->getParameterValue(self::PARAM_OFFSET, 0);
        $this->pageBy = $this->getParameterValue(self::PARAM_PAGEBY, self::PARAM_DEFAULT_PAGEBY);
        $this->pageLimit = $this->getParameterValue(self::PARAM_LIMIT, null);
        $this->autoMarkdown = $this->getParameterValue(self::PARAM_AUTO_MARKDOWN, true);
    }

    /**
     * Applies Antlers specific filter restrictions.
     */
    private function applyFilterRestrictions()
    {
        $this->filterManager->restrictFilter('thread:in', [
            'meerkat:all-comments'
        ]);
    }

    /**
     * Creates a new RuntimeContext instance and returns it.
     *
     * @return RuntimeContext
     */
    private function getRuntimeContext()
    {
        $context = new RuntimeContext();
        $context->parameters = $this->getParameterArray();
        $context->context = $this->context->toArray();

        return $context;
    }

    /**
     * Parses the provided tag parameters and applies any filters to the current data query.
     */
    private function applyParamFiltersToQuery()
    {
        $paramFilters = $this->getFiltersFromParams();
        $filterString = $this->getParameterValue(CollectionRenderer::PARAM_FILTER, null);

        if ($filterString !== null && mb_strlen(trim($filterString)) > 0) {
            $parsedFilters = $this->filterManager->parseFilterString($filterString);

            if ($parsedFilters !== null && is_array($parsedFilters)) {
                $paramFilters = array_merge($paramFilters, $parsedFilters);
            }
        }

        unset($filterString);

        if (count($paramFilters) === 0) {
            return;
        }

        $primaryFilter = array_shift($paramFilters);

        $this->query->filterBy($primaryFilter);

        if (count($paramFilters) > 0) {
            foreach ($paramFilters as $filter) {
                $this->query->thenFilterBy($filter);
            }
        }
    }

    /**
     * Parses the Antlers parameters and converts them to filter expressions.
     *
     * @return array
     */
    private function getFiltersFromParams()
    {
        $filters = [];

        if (TypeConversions::getBooleanValue(
                $this->getParameterValue(CollectionRenderer::PARAM_INCLUDE_SPAM, false)
            ) === false) {
            $filters['is:spam'] = 'is:spam(false)';
        }

        if (TypeConversions::getBooleanValue(
                $this->getParameterValue(CollectionRenderer::PARAM_UNAPPROVED, false)
            ) === false) {
            $filters['is:published'] = 'is:published(true)';
        }

        $untilFilter = $this->getParameterValue(CollectionRenderer::PARAM_UNTIL, null);
        $sinceFilter = $this->getParameterValue(CollectionRenderer::PARAM_SINCE, null);

        if ($untilFilter !== null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters['is:between'] = 'is:between(' . $sinceDate . ',' . $untilDate . ')';
        } elseif ($untilFilter === null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);

            $filters['is:after'] = 'is:after(' . $sinceDate . ')';
        } elseif ($untilFilter !== null && $sinceFilter === null) {
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters['is:before'] = 'is:before(' . $untilFilter . ')';
        }

        return $filters;
    }

    private function getDateTimeTimestamp($value)
    {
        $timestampValue = $value;

        if (is_string($value) && mb_strlen(trim($value)) == 10) {
            $timestampValue = $value;
        } else {
            $timestampValue = Carbon::parse($timestampValue)->timestamp;
        }

        return $timestampValue;
    }

    /**
     * Parses the Antlers order parameter and uses them to build up the sorting predicate.
     */
    private function applyParamOrdersToQuery()
    {
        $paramOrders = $this->getParameterValue(CollectionRenderer::PARAM_ORDER, null);
        $orders = [];

        if ($paramOrders === null || mb_strlen(trim($paramOrders)) === 0) {
            $paramOrders = 'id,desc';
        }

        $tempOrders = explode('|', $paramOrders);

        foreach ($tempOrders as $order) {
            $orderParts = explode(',', $order);

            if (count($orderParts) === 0) {
                continue;
            } elseif (count($orderParts) === 1) {
                $orders[] = [
                    PredicateBuilder::KEY_PROPERTY => trim($orderParts[0]),
                    PredicateBuilder::KEY_IS_ASC => true
                ];
            } else {
                $ascending = true;

                if (mb_strtolower(trim($orderParts[1])) === 'desc') {
                    $ascending = false;
                }

                $orders[] = [
                    PredicateBuilder::KEY_PROPERTY => trim($orderParts[0]),
                    PredicateBuilder::KEY_IS_ASC => $ascending
                ];
            }
        }

        unset($tempOrders);

        if (count($orders) === 0) {
            return;
        }

        $primaryOrder = array_shift($orders);

        if ($primaryOrder[PredicateBuilder::KEY_IS_ASC]) {
            $this->query->sortAsc($primaryOrder[PredicateBuilder::KEY_PROPERTY]);
        } else {
            $this->query->sortDesc($primaryOrder[PredicateBuilder::KEY_PROPERTY]);
        }

        foreach ($orders as $order) {
            if ($order[PredicateBuilder::KEY_IS_ASC]) {
                $this->query->thenSortAsc($order[PredicateBuilder::KEY_PROPERTY]);
            } else {
                $this->query->thenSortDesc($order[PredicateBuilder::KEY_PROPERTY]);
            }
        }
    }

    /**
     * Removes any Antlers specific filter restrictions.
     */
    private function removeFilterRestrictions()
    {
        $this->filterManager->restrictFilter('thread:in', []);
    }

    /**
     * Prepares a paginated, grouped dataset for rendering.
     *
     * @param PagedGroupedDataSetContract $dataset The grouped, and paged dataset.
     * @return array
     */
    private function renderPaginatedGroupedDataset($dataset)
    {
        if ($this->autoMarkdown) {
            $dataset->mutate(function ($comment) {
                if (is_array($comment) && array_key_exists(CommentContract::KEY_COMMENT_MARKDOWN, $comment)) {
                    $comment[CommentContract::KEY_CONTENT] = $comment[CommentContract::KEY_COMMENT_MARKDOWN];
                }

                return $comment;
            });
        }

        $totalResults = $dataset->count();
        $hasResults = $totalResults > 0;

        $groupData = $dataset->getData();

        $groups = array_values($groupData[$dataset->getCollectiveGroupName()]);
        $groupData[$dataset->getCollectiveGroupName()] = $groups;

        return [
            $dataset->getCollectiveGroupName() => $groups,
            PagedDataSet::KEY_PAGINATE => $dataset->getAdditionalMetaData(),
            self::RETURN_TOTAL_RESULTS => $totalResults,
            self::RETURN_HAS_RESULTS => $hasResults,
            self::RETURN_NO_RESULTS => !$hasResults,
            self::RETURN_ITEMS_COUNT => $dataset->getItemsCount()
        ];
    }

    /**
     * Prepares the grouped dataset for rendering.
     *
     * @param GroupedDataSetContract $dataset The grouped dataset.
     * @return array
     */
    private function renderGroupedComments($dataset)
    {
        if ($this->autoMarkdown) {
            $dataset->mutate(function ($comment) {
               if (is_array($comment) && array_key_exists(CommentContract::KEY_COMMENT_MARKDOWN, $comment)) {
                   $comment[CommentContract::KEY_CONTENT] = $comment[CommentContract::KEY_COMMENT_MARKDOWN];
               }

               return $comment;
            });
        }

        $totalItems = $dataset->count();
        $hasResults = $totalItems > 0;
        $groupData = $dataset->getData();

        $groupData = array_merge($groupData, [
            self::RETURN_HAS_RESULTS => $hasResults,
            self::RETURN_NO_RESULTS => !$hasResults,
            self::RETURN_TOTAL_RESULTS => $totalItems,
            self::RETURN_ITEMS_COUNT => $totalItems
        ]);

        // Removes group keys before returning the data to the view engine.
        $groups = array_values($groupData[$dataset->getCollectiveGroupName()]);
        $groupData[$dataset->getCollectiveGroupName()] = $groups;

        return $groupData;
    }

    /**
     * Prepares the paginated dataset for rendering.
     *
     * @param PagedDataSetContract $dataset The paginated dataset.
     * @param string $collectionName The name of the collection.
     * @return array
     */
    private function renderPaginatedComments($dataset, $collectionName)
    {
        $totalResults = $dataset->count();
        $hasResults = $totalResults > 0;

        $displayItems = $dataset->getDisplayItems();

        if ($this->autoMarkdown) {
            foreach ($displayItems as &$comment) {
                if (array_key_exists(CommentContract::KEY_COMMENT_MARKDOWN, $comment)) {
                    $comment[CommentContract::KEY_CONTENT] = $comment[CommentContract::KEY_COMMENT_MARKDOWN];
                }
            }
        }

        return [
            $collectionName => $displayItems,
            PagedDataSet::KEY_PAGINATE => $dataset->getAdditionalMetaData(),
            self::RETURN_TOTAL_RESULTS => $totalResults,
            self::RETURN_HAS_RESULTS => $hasResults,
            self::RETURN_NO_RESULTS => !$hasResults,
            self::RETURN_ITEMS_COUNT => $dataset->getItemsCount()
        ];
    }

    /**
     * @param $comments
     * @param $collectionName
     * @param $isFlatList
     * @return string
     */
    private function renderListComments($comments, $collectionName, $isFlatList)
    {
        $displayComments = [];

        if ($this->autoMarkdown) {
            foreach ($comments as &$comment) {
                if (array_key_exists(CommentContract::KEY_COMMENT_MARKDOWN, $comment)) {
                    $comment[CommentContract::KEY_CONTENT] = $comment[CommentContract::KEY_COMMENT_MARKDOWN];
                }
            }
        }

        if ($isFlatList === false) {
            foreach ($comments as $comment) {
                if ($comment[CommentContract::KEY_DEPTH] === 1) {
                    $displayComments[] = $comment;
                }
            }
        } else {
            $displayComments = $comments;
        }

        return $this->parseComments([
            $collectionName => $displayComments
        ], [], $collectionName);
    }

    /**
     * Renders a recursive thread.
     *
     * @param array $data The comment data.
     * @param array $context The render context.
     * @param string $collectionName The name of the collection.
     * @return string|string[]
     */
    protected function parseComments($data = [], $context = [], $collectionName = 'comments')
    {
        $metaData = [];
        $totalResults = count($data[$collectionName]);
        $hasResults = $totalResults > 0;

        if (array_key_exists('total_results', $data) === false) {
            $metaData = [
                self::RETURN_TOTAL_RESULTS => $totalResults,
                self::RETURN_ITEMS_COUNT => $totalResults,
                self::RETURN_HAS_RESULTS => $hasResults,
                self::RETURN_NO_RESULTS => !$hasResults
            ];
        }

        if (count($metaData) > 0) {
            $data = array_merge($data, $metaData);
        }

        return RecursiveThreadRenderer::renderRecursiveThread(
            $this->sanitizer, $this->content, $data, $context, $collectionName
        );
    }

}
