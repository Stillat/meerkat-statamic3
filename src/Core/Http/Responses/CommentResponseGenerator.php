<?php

namespace Stillat\Meerkat\Core\Http\Responses;

use Stillat\Meerkat\Core\Authoring\InitialsGenerator;
use Stillat\Meerkat\Core\Authoring\TransientIdGenerator;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\IsFilters;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Where;
use Stillat\Meerkat\Core\Data\Retrievers\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Parsing\ExpressionParser;

/**
 * Class CommentResponseGenerator
 *
 * Provides mechanisms for generating API response values.
 *
 * @since 2.0.0
 */
class CommentResponseGenerator
{
    const KEY_API_COMMENT_COLLECTION = 'comments';

    const KEY_API_AUTHOR_COLLECTION = 'authors';

    const KEY_API_THREAD_COLLECTION = 'threads';

    const KEY_API_SORT_ORDERS = 'orders';

    const KEY_API_TERMS = 'terms';

    const KEY_API_PAGE_METADATA = 'pages';

    const KEY_API_FILTERS = 'filters';

    const KEY_PARAM_PAGE = 'page';

    const KEY_PARAM_RESULTS_PER_PAGE = 'resultsPerPage';

    const VALUE_DEFAULT_PER_PAGE = 10;

    /**
     * The CommentManagerContract implementation instance.
     *
     * @var CommentManagerContract
     */
    protected $manager = null;

    /**
     * The data query to utilize when preparing the response.
     *
     * @var DataQuery
     */
    protected $query = null;

    /**
     * The ContextResolverContract implementation instance.
     *
     * @var ContextResolverContract
     */
    protected $resolver = null;

    /**
     * The empty RuntimeContext instance to use in queries.
     *
     * @var RuntimeContext
     */
    protected $runtimeContext = null;

    /**
     * The ExpressionParser instance.
     *
     * @var ExpressionParser
     */
    protected $expressionParser = null;

    /**
     * The properties to remove from the API response.
     *
     * @var array
     */
    protected $propertiesToRemove = [];

    public function __construct(CommentManagerContract $manager,
                                DataQuery $query,
                                ContextResolverContract $resolver,
                                CommentFilterManager $filterManager,
                                ExpressionParser $expressionParser)
    {
        $this->manager = $manager;
        $this->query = $query;
        $this->resolver = $resolver;
        $this->runtimeContext = new RuntimeContext();
        $this->propertiesToRemove = $this->getPropertiesToRemove();
        $this->expressionParser = $expressionParser;

        $this->expressionParser->setFilterGroups($filterManager->getFilterGroups());
    }

    /**
     * Gets a collection of all properties to remove.
     *
     * @return array
     */
    private function getPropertiesToRemove()
    {
        return [
            CommentContract::KEY_EMAIL,
            CommentContract::INTERNAL_PATH,
            CommentContract::KEY_USER_IP,
            CommentContract::KEY_USER_AGENT,
            CommentContract::KEY_REFERRER,
            CommentContract::KEY_PAGE_URL,
            CommentContract::KEY_NAME,
            CommentContract::INTERNAL_HAS_COLLECTED,
            AuthorContract::KEY_AUTHOR_URL,
        ];
    }

    /**
     * Updates the internal query state from request parameters.
     *
     * @param  array  $parameters The query parameters.
     */
    public function updateFromParameters($parameters)
    {
        $requestOrders = [];

        if (array_key_exists('terms', $parameters) && mb_strlen(trim($parameters['terms'])) > 0) {
            $this->query->searchFor(trim($parameters['terms']));
        }

        if (array_key_exists('order', $parameters) && mb_strlen(trim($parameters['order'])) > 0) {
            $requestOrders = explode('|', $parameters['order']);
        }

        for ($i = 0; $i < count($requestOrders); $i++) {
            $orderParts = explode(',', $requestOrders[$i]);
            $dataPoint = trim($orderParts[0]);

            if ($dataPoint === 'date') {
                $dataPoint = 'id';
            }

            if ($dataPoint === 'comment') {
                $dataPoint = 'content_raw';
            }

            if (count($orderParts) === 2) {
                $direction = mb_strtolower(trim($orderParts[1]));

                if ($direction === 'asc') {
                    if ($i === 0) {
                        $this->query->sortAsc($dataPoint);
                    } else {
                        $this->query->thenSortAsc($dataPoint);
                    }
                } elseif ($direction === 'desc') {
                    if ($i === 0) {
                        $this->query->sortDesc($dataPoint);
                    } else {
                        $this->query->thenSortDesc($dataPoint);
                    }
                }
            }
        }

        $filterString = '';

        if (array_key_exists('filter', $parameters) && mb_strlen(trim($parameters['filter'])) > 0) {
            $filterString = $parameters['filter'];
        }

        $requestFilters = [];
        $requestFilters = $this->expressionParser->parse($filterString);

        if (count($requestFilters) === 0) {
            $requestFilters[] = ExpressionParser::buildFilterArray(IsFilters::FILTER_IS_DELETED, ['false']);
        }

        $requestFilters[] = ExpressionParser::buildFilterArray(Where::FILTER_WHERE, [
            'parser_has_supplemented_data', '!==', 'true',
        ]);

        $firstFilter = array_shift($requestFilters);
        $this->query->withContext($this->runtimeContext);

        $this->query->safeFilterBy($firstFilter);

        if (count($requestFilters) > 0) {
            foreach ($requestFilters as $filter) {
                $this->query->safeThenFilterBy($filter);
            }
        }

        $this->query->pageBy('page');

        if (array_key_exists(CommentResponseGenerator::KEY_PARAM_PAGE, $parameters)) {
            $this->query->forPage(intval($parameters[CommentResponseGenerator::KEY_PARAM_PAGE]));
        } else {
            $this->query->forPage(1);
        }

        if (array_key_exists(CommentResponseGenerator::KEY_PARAM_RESULTS_PER_PAGE, $parameters)) {
            $this->query->limit(intval($parameters[CommentResponseGenerator::KEY_PARAM_RESULTS_PER_PAGE]));
        } else {
            $this->query->limit(CommentResponseGenerator::VALUE_DEFAULT_PER_PAGE);
        }
    }

    /**
     * Constructs an array suitable for returning as an API response.
     *
     * @return array
     *
     * @throws FilterException
     */
    public function getApiResponse()
    {
        $queryResults = $this->manager->queryAll($this->query);
        $commentResults = $queryResults->flattenDataset();

        $responseAuthors = CommentAuthorRetriever::getAuthorsFromCommentArray($commentResults);
        $threads = [];

        /** @var array $comment */
        foreach ($commentResults as $key => $comment) {
            $threadId = null;

            if (array_key_exists(CommentContract::INTERNAL_CONTEXT_ID, $comment)) {
                $threadId = $comment[CommentContract::INTERNAL_CONTEXT_ID];
            }
            if ($threadId !== null && array_key_exists($threadId, $threads) === false) {
                $threads[$threadId] = $this->resolver->findById($threadId);
            }

            $commentResults[$key]['_cp_view_entry_url'] = route('statamic.cp.meerkat.redirect', [
                $comment['internal_context_id'], $comment['id'],
            ]);
        }

        /** @var array $author */
        foreach ($responseAuthors as &$author) {
            unset($author[AuthorContract::KEY_PERMISSIONS]);
            unset($author[AuthorContract::KEY_USER]);

            $author[AuthorContract::KEY_INITIALS] = InitialsGenerator::getInitials($author[AuthorContract::KEY_NAME]);
        }

        $commentsToReturn = array_values($commentResults);

        unset($commentResults);

        // Remaps various properties to help reduce the total data size of the return value.
        foreach ($commentsToReturn as &$comment) {
            $comment = $this->getApiComment($comment);
        }

        $pageMetaData = PaginationMetaDataResponseGenerator::getApiResponse($queryResults->getMetaData());

        $filterString = ExpressionParser::convertToString($this->query->getFilters());

        return [
            CommentResponseGenerator::KEY_API_AUTHOR_COLLECTION => $responseAuthors,
            CommentResponseGenerator::KEY_API_COMMENT_COLLECTION => $commentsToReturn,
            CommentResponseGenerator::KEY_API_THREAD_COLLECTION => array_values($threads),
            CommentResponseGenerator::KEY_API_SORT_ORDERS => $this->query->getSortString(),
            CommentResponseGenerator::KEY_API_TERMS => $this->query->getTerms(),
            CommentResponseGenerator::KEY_API_PAGE_METADATA => $pageMetaData,
            CommentResponseGenerator::KEY_API_FILTERS => $filterString,
        ];
    }

    public function getApiComment($comment)
    {
        if ($comment[CommentContract::KEY_IS_PARENT] === true) {
            if (array_key_exists(CommentContract::KEY_CHILDREN, $comment)) {
                unset($comment[CommentContract::KEY_CHILDREN]);
            }

            if (array_key_exists(CommentResponseGenerator::KEY_API_COMMENT_COLLECTION, $comment)) {
                if (is_array($comment[CommentResponseGenerator::KEY_API_COMMENT_COLLECTION])) {
                    $childrenIds = [];

                    foreach ($comment[CommentResponseGenerator::KEY_API_COMMENT_COLLECTION] as $childComment) {
                        if (is_array($childComment)) {
                            if (array_key_exists(CommentContract::KEY_ID, $childComment)) {
                                $childrenIds[] = $childComment[CommentContract::KEY_ID];
                            }
                        }
                    }

                    $comment[CommentResponseGenerator::KEY_API_COMMENT_COLLECTION] = $childrenIds;
                } else {
                    $comment[CommentResponseGenerator::KEY_API_COMMENT_COLLECTION] = [];
                }
            }
        }

        if (array_key_exists(CommentContract::KEY_PARENT, $comment) && $comment[CommentContract::KEY_PARENT] !== null) {
            if ($comment[CommentContract::KEY_PARENT] instanceof CommentContract) {
                $comment[CommentContract::KEY_PARENT] = $comment[CommentContract::KEY_PARENT]->getId();
            } elseif (is_array($comment[CommentContract::KEY_PARENT])) {
                if (array_key_exists(CommentContract::KEY_ID, $comment[CommentContract::KEY_PARENT])) {
                    $comment[CommentContract::KEY_PARENT] = $comment[CommentContract::KEY_PARENT][CommentContract::KEY_ID];
                } else {
                    unset($comment[CommentContract::KEY_PARENT]);
                }
            } else {
                unset($comment[CommentContract::KEY_PARENT]);
            }
        } else {
            unset($comment[CommentContract::KEY_PARENT]);
        }

        // Process the primary author.
        $comment[CommentContract::KEY_AUTHOR] = $this->getAuthorIdentificationInformation(CommentContract::KEY_AUTHOR, $comment);
        $comment[CommentContract::INTERNAL_PARENT_AUTHOR] = $this->getAuthorIdentificationInformation(CommentContract::INTERNAL_PARENT_AUTHOR, $comment);

        if (array_key_exists(CommentContract::INTERNAL_CONTEXT, $comment)) {
            $comment[CommentContract::INTERNAL_CONTEXT] = $comment[CommentContract::INTERNAL_CONTEXT_ID];
        }

        foreach ($this->propertiesToRemove as $property) {
            if (array_key_exists($property, $comment)) {
                unset($comment[$property]);
            }
        }

        $comment[CommentContract::KEY_HAS_CHECKED_FOR_SPAM] = array_key_exists(CommentContract::KEY_SPAM, $comment);

        return $comment;
    }

    /**
     * Attempts to locate the requested author information in the comment.
     *
     * @param  string  $authorKey The author key.
     * @param  array  $comment The comment array to analyze.
     * @return string|null
     */
    private function getAuthorIdentificationInformation($authorKey, $comment)
    {
        if (array_key_exists($authorKey, $comment)) {
            if ($comment[$authorKey] === null) {
                return null;
            }

            if (array_key_exists(AuthorContract::KEY_HAS_USER, $comment[$authorKey])) {
                if ($comment[$authorKey][AuthorContract::KEY_HAS_USER] === true) {
                    if (array_key_exists(AuthorContract::KEY_USER_ID, $comment[$authorKey])) {
                        return (string) $comment[$authorKey][AuthorContract::KEY_USER_ID];
                    } else {
                        return null;
                    }
                } else {
                    return TransientIdGenerator::getId($comment[$authorKey]);
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Returns the comments from the request.
     *
     * @return CommentContract[]
     *
     * @throws FilterException
     */
    public function getRequestComments()
    {
        $results = $this->manager->queryAll($this->query);

        return $results->getData();
    }
}
