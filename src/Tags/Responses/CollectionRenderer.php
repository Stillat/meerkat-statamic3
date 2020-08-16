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
use Stillat\Meerkat\Core\Data\PredicateBuilder;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Tags\MeerkatTag;
use Stillat\Meerkat\Tags\Output\PaginatedThreadRenderer;
use Stillat\Meerkat\Tags\Output\RecursiveThreadRenderer;

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
    private $paginatedThreadRenderer = null;

    public function __construct(
        ThreadManagerContract $threadManager,
        CommentFilterManager $filterManager,
        SanitationManagerContract $sanitizer,
        PaginatedThreadRenderer $pageRenderer,
        DataQuery $query)
    {
        $this->query = $query;
        $this->filterManager = $filterManager;
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
        $this->paginatedThreadRenderer = $pageRenderer;
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
     * @return string
     * @throws FilterException
     */
    public function render()
    {
        $this->parseParameters();

        $collectionName = $this->getParam(
            CollectionRenderer::PARAM_COLLECTION_ALIAS, CollectionRenderer::DEFAULT_COLLECTION_NAME
        );

        $this->applyFilterRestrictions();

        $runtimeContext = $this->getRuntimeContext();
        $runtimeContext->templateTagContext = $this->tagContext;

        $this->query->withContext($runtimeContext);

        $flatList = $this->getParam(CollectionRenderer::PARAM_FLAT, false);

        $this->applyParamFiltersToQuery();
        $this->applyParamOrdersToQuery();

        // TODO: Non-date groups?
        // TODO: since: filter
        // TODO: until: filter

        $thread = $this->threadManager->findById($this->threadId);

        $displayComments = [];
        $comments = $thread->getComments();

        $this->query->limit($this->pageLimit)->skip($this->pageOffset);

        if ($this->paginated) {
            $currentPage = request()->input($this->pageBy, 0);

            $this->query->pageBy($this->pageBy)->forPage($currentPage);
        }

        if ($this->get('group_by_date')) {
            $dateFormat = $this->get('group_by_date');

            $this->query->nameAllGroups('date_groups')->groupName('date_group')
                ->collectionName($collectionName)->groupBy('group:date', function (CommentContract $comment) use ($dateFormat) {
                    $comment->setDataAttribute('group:date', $comment->getCommentDate()->format($dateFormat));
                });
        }

        $result = $this->query->getCollection($comments, $collectionName);

        $this->removeFilterRestrictions();

        if ($result instanceof GroupedDataSetContract) {
            if ($result instanceof PagedGroupedDataSetContract) {
                return '';
            }

            return '';
        } elseif ($result instanceof PagedDataSetContract) {
            return '';
        } elseif ($result instanceof DataSetContract) {
            return $this->renderListComments($result->getData(), $collectionName, $flatList);
        }

        // TODO: Re-implement the views.

        foreach ($result as $result) {
            dd('111', $result);
        }

        dd('adsf', $result);

        if ($flatList === true) {
            $displayComments = $comments;
        } else {
            foreach ($comments as $comment) {
                if ($comment[CommentContract::KEY_DEPTH] === 1) {
                    $displayComments[] = $comment;
                }
            }
        }

        if ($this->paginated) {
            return $this->parseComments(
                $this->paginatedThreadRenderer->preparePaginatedThread($collectionName,
                    collect($displayComments),
                    $this->pageBy,
                    $this->pageOffset,
                    $this->pageLimit)
                ,
                [], $collectionName);
        } else {
            return $this->parseComments([
                $collectionName => $displayComments
            ], [], $collectionName);
        }
    }

    /**
     * Parses the tag's current context and sets the internal state.
     */
    private function parseParameters()
    {
        $this->paginated = $this->getParam('paginate', false);
        $this->pageOffset = $this->getParam('offset', 0);
        $this->pageBy = $this->getParam('pageby', 'page');
        $this->pageLimit = $this->getParam('limit', null);
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
        $context->parameters = $this->parameters;
        $context->context = $this->context->toArray();

        return $context;
    }

    /**
     * Parses the provided tag parameters and applies any filters to the current data query.
     */
    private function applyParamFiltersToQuery()
    {
        $paramFilters = $this->getFiltersFromParams();
        $filterString = $this->getParam(CollectionRenderer::PARAM_FILTER, null);

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

        if ($this->getBool(CollectionRenderer::PARAM_INCLUDE_SPAM, false) === false) {
            $filters['is:spam'] = 'is:spam(false)';
        }

        if ($this->getBool(CollectionRenderer::PARAM_UNAPPROVED, false) === false) {
            $filters['is:published'] = 'is:published(true)';
        }

        $untilFilter = $this->getParam(CollectionRenderer::PARAM_UNTIL, null);
        $sinceFilter = $this->getParam(CollectionRenderer::PARAM_SINCE, null);

        if ($untilFilter !== null && $sinceFilter !== null) {
            $filters['is:between'] = 'is:between(' . $sinceFilter . ',' . $untilFilter . ')';
        } elseif ($untilFilter === null && $sinceFilter !== null) {
            $filters['is:after'] = 'is:after(' . $sinceFilter . ')';
        } elseif ($untilFilter !== null && $sinceFilter === null) {
            $filters['is:before'] = 'is:before(' . $untilFilter . ')';
        }

        return $filters;
    }

    /**
     * Parses the Antlers order parameter and uses them to build up the sorting predicate.
     */
    private function applyParamOrdersToQuery()
    {
        $paramOrders = $this->getParam(CollectionRenderer::PARAM_ORDER, null);
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
     * @param $comments
     * @param $collectionName
     * @param $isFlatList
     * @return string
     */
    private function renderListComments($comments, $collectionName, $isFlatList)
    {
        $displayComments = [];

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

        if (array_key_exists('total_results', $data) === false) {
            $metaData = [
                'total_results' => count($data[$collectionName])
            ];
        }

        if (count($metaData) > 0) {
            $data = array_merge($data, $metaData);
        }

        return RecursiveThreadRenderer::renderRecursiveThread($this->sanitizer, $this->content, $data, $context, $collectionName);
    }

}
