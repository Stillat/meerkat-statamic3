<?php

namespace Stillat\Meerkat\Tags\Responses;

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
use Stillat\Meerkat\Core\Exceptions\FilterParserException;
use Stillat\Meerkat\Core\Exceptions\ParserException;
use Stillat\Meerkat\Core\Parsing\ExpressionParser;
use Stillat\Meerkat\Tags\MeerkatTag;

/**
 * Class CollectionRenderer
 *
 * Responsible for managing the interactions between Meerkat and Statamic's Antlers templating engine.
 *
 * @ls noparse
 *
 * @since 2.0.0
 */
class CollectionRenderer extends MeerkatTag
{
    const PARAM_UNAPPROVED = 'include_unapproved';

    const PARAM_INCLUDE_SPAM = 'include_spam';

    const PARAM_WITH_TRASHED = 'with_trashed';

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

    /**
     * The current tag filter context, if any.
     *
     * @var string
     */
    public $tagContext = '';

    /**
     * The ThreadManagerContract implementation instance.
     *
     * @var ThreadManagerContract
     */
    protected $threadManager = null;

    /**
     * The SanitationManagerContract implementation instance.
     *
     * @var SanitationManagerContract
     */
    protected $sanitizer = null;

    /**
     * Indicates if the result set should be paginated.
     *
     * @var bool
     */
    protected $paginated = false;

    /**
     * The maximum number of items per page, if any.
     *
     * @var int|null
     */
    protected $pageLimit = null;

    /**
     * The current page offset, if any.
     *
     * @var int
     */
    protected $pageOffset = 0;

    /**
     * The URL parameter that indicates what the current pagination page is.
     *
     * @var string
     */
    protected $pageBy = 'page';

    /**
     * The current DataQuery instance, if any.
     *
     * @var DataQuery|null
     */
    protected $query = null;

    /**
     * The current thread identifier, if any.
     *
     * @var string|null
     */
    private $threadId = null;

    /**
     * Indicates if content should be processed as Markdown automatically.
     *
     * @var bool
     */
    private $autoMarkdown = false;

    public function __construct(
        ThreadManagerContract $threadManager,
        CommentFilterManager $filterManager,
        SanitationManagerContract $sanitizer,
        DataQuery $query, ExpressionParser $expressionParser)
    {
        parent::__construct($filterManager, $expressionParser);

        $this->query = $query;
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Sets the internal thread identifier.
     *
     * @param  string  $threadId The thread identifier.
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;

        if (is_object($this->threadId)) {
            $this->threadId = (string) $this->threadId;
        }
    }

    /**
     * Renders the tag content.
     *
     * @return string|array
     *
     * @throws FilterException
     * @throws FilterParserException
     * @throws ParserException
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

        $this->applyParamFiltersToQuery($this->query);
        $this->applyParamOrdersToQuery();

        $thread = $this->threadManager->findById($this->threadId);

        $comments = [];

        if ($thread !== null) {
            $comments = $thread->getComments();
        }

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
            'meerkat:all-comments',
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
                    PredicateBuilder::KEY_IS_ASC => true,
                ];
            } else {
                $ascending = true;

                if (mb_strtolower(trim($orderParts[1])) === 'desc') {
                    $ascending = false;
                }

                $orders[] = [
                    PredicateBuilder::KEY_PROPERTY => trim($orderParts[0]),
                    PredicateBuilder::KEY_IS_ASC => $ascending,
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
     * Prepares a paginated, grouped dataset for rendering.
     *
     * @param  PagedGroupedDataSetContract  $dataset The grouped, and paged dataset.
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
            self::RETURN_NO_RESULTS => ! $hasResults,
            self::RETURN_ITEMS_COUNT => $dataset->getItemsCount(),
        ];
    }

    /**
     * Prepares the grouped dataset for rendering.
     *
     * @param  GroupedDataSetContract  $dataset The grouped dataset.
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
            self::RETURN_NO_RESULTS => ! $hasResults,
            self::RETURN_TOTAL_RESULTS => $totalItems,
            self::RETURN_ITEMS_COUNT => $totalItems,
        ]);

        // Removes group keys before returning the data to the view engine.
        $groups = array_values($groupData[$dataset->getCollectiveGroupName()]);
        $groupData[$dataset->getCollectiveGroupName()] = $groups;

        return $groupData;
    }

    /**
     * Prepares the paginated dataset for rendering.
     *
     * @param  PagedDataSetContract  $dataset The paginated dataset.
     * @param  string  $collectionName The name of the collection.
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
            self::RETURN_NO_RESULTS => ! $hasResults,
            self::RETURN_ITEMS_COUNT => $dataset->getItemsCount(),
        ];
    }

    /**
     * Renders the provided list of comments.
     *
     * @param  array  $comments The comments to render.
     * @param  string  $collectionName The collection name.
     * @param  bool  $isFlatList Indicates if the results should be a flat list.
     * @return array
     */
    private function renderListComments($comments, $collectionName, $isFlatList)
    {
        $displayComments = [];

        if ($this->autoMarkdown) {
            $comments = $this->recursivelyApplyMarkdown($comments, $collectionName);
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
            $collectionName => $displayComments,
        ], $this->context->toArray(), $collectionName);
    }

    /**
     * Recursively processes the comment's content as Markdown.
     *
     * @param  array  $comments The comments to apply Markdown processing to.
     * @param  string  $collectionName The inner collection name.
     * @return array
     */
    private function recursivelyApplyMarkdown($comments, $collectionName)
    {
        foreach ($comments as $commentId => $comment) {
            if (array_key_exists($collectionName, $comment)) {
                $comments[$commentId][$collectionName] = $this->recursivelyApplyMarkdown(
                    $comments[$commentId][$collectionName], $collectionName
                );
            }

            if (array_key_exists(CommentContract::KEY_COMMENT_MARKDOWN, $comment)) {
                $comments[$commentId][CommentContract::KEY_CONTENT] = $comment[CommentContract::KEY_COMMENT_MARKDOWN];
            }
        }

        return $comments;
    }

    /**
     * Renders a recursive thread.
     *
     * @param  array  $data The comment data.
     * @param  array  $context The render context.
     * @param  string  $collectionName The name of the collection.
     * @return array
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
                self::RETURN_NO_RESULTS => ! $hasResults,
            ];
        }

        if (count($metaData) > 0) {
            $data = array_merge($data, $metaData);
        }

        return $data;
    }
}
