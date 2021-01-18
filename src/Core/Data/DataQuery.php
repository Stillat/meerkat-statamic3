<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Data\Converters\DataSetCollectionConverter;
use Stillat\Meerkat\Core\Data\Converters\GroupedCollectionConverter;
use Stillat\Meerkat\Core\Data\Converters\PagedCollectionConverter;
use Stillat\Meerkat\Core\Data\Converters\PagedGroupedCollectionConverter;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Like;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\NotLike;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Where;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\WhereIn;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\WhereNotIn;
use Stillat\Meerkat\Core\Data\Filters\FilterRunner;
use Stillat\Meerkat\Core\Data\Retrievers\CommentIdRetriever;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\FilterParserException;
use Stillat\Meerkat\Core\Exceptions\ParserException;
use Stillat\Meerkat\Core\Parsing\ExpressionParser;
use Stillat\Meerkat\Core\Parsing\MarkdownParserFactory;
use Stillat\Meerkat\Core\Search\Engine;
use Stillat\Meerkat\Core\Search\Providers\BitapSearchProvider;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\InternalAttributes;

/**
 * Class DataQuery
 *
 * Provides a fluent interface for querying Meerkat comments.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class DataQuery
{

    /**
     * A list of potential filters to run.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The runtime context that the data query executes in.
     *
     * @var RuntimeContext|null
     */
    protected $runtimeContext = null;

    /**
     * The FilterRunner instance.
     *
     * @var FilterRunner
     */
    protected $filterRunner = null;

    /**
     * The sort predicate builder.
     *
     * @var PredicateBuilder
     */
    private $sortPredicateBuilder = null;

    /**
     * The PaginatorContract implementation instance.
     *
     * @var PaginatorContract
     */
    private $paginator = null;

    /**
     * Indicates if the result set will be paginated.
     *
     * @var bool
     */
    private $isPaged = false;

    /**
     * Indicates if the result set will be grouped.
     *
     * @var bool
     */
    private $isGrouped = false;

    /**
     * The name of pages, in a paged result set.
     *
     * @var string
     */
    private $pageName = 'page';

    /**
     * The current page, in a paged result set.
     *
     * @var int
     */
    private $currentPage = 0;

    /**
     * The number of records to skip when processing data.
     *
     * @var int
     */
    private $dataOffset = 0;

    /**
     * The number of records to restrict the result set to.
     *
     * @var int|null
     */
    private $dataLimit = null;

    /**
     * The property name to group the dataset by.
     *
     * @var null|string
     */
    private $groupBy = null;

    /**
     * An optional callback used to generate a dynamic group property.
     *
     * @var null|callable
     */
    private $groupCallback = null;

    /**
     * The name of an individual group's dataset.
     *
     * @var null|string
     */
    private $groupCollectionName = null;

    /**
     * The name of an individual group.
     *
     * @var null|string
     */
    private $groupName = null;

    /**
     * The name given to a collection of groups.
     *
     * @var null|string
     */
    private $groupCollectiveName = null;

    /**
     * Indicates if empty groups should be included in a grouped dataset.
     *
     * @var bool
     */
    private $groupKeepEmptyResults = false;

    /**
     * Indicates if meta data should be collected before paging takes place.
     *
     * @var bool
     */
    private $pagesGetMetaDataBeforePaging = false;

    /**
     * A search Engine implementation instance.
     *
     * @var Engine
     */
    private $searchEngine = null;

    /**
     * The SanitationManagerContract implementation instance.
     *
     * @var SanitationManagerContract
     */
    private $sanitationManager = null;

    /**
     * The GroupedCollectionConverter instance.
     *
     * @var GroupedCollectionConverter
     */
    private $groupedCollectionConverter = null;

    /**
     * The PagedGroupedCollectionConverter instance.
     *
     * @var PagedGroupedCollectionConverter
     */
    private $pagedGroupedCollectionConverter = null;

    /**
     * The PagedCollectionConverter instance.
     *
     * @var PagedCollectionConverter
     */
    private $pagedCollectionConverter = null;

    /**
     * The DataSetCollectionConverter instance.
     *
     * @var DataSetCollectionConverter
     */
    private $basicDataSetConverter = null;

    /**
     * Indicates if content should be parsed as Markdown automatically.
     *
     * @var bool
     */
    private $returnWithMarkdown = false;

    /**
     * Optional search terms.
     *
     * If set, will be converted to a filter.
     *
     * @var null|string
     */
    private $searchTerms = null;

    /**
     * The ExpressionParser instance.
     *
     * @var ExpressionParser
     */
    private $expressionParser = null;

    public function __construct(
        Configuration $config,
        PaginatorContract $paginator,
        FilterRunner $filterRunner,
        SanitationManagerContract $sanitationManager,
        ExpressionParser $expressionParser)
    {
        $this->filterRunner = $filterRunner;
        $this->sortPredicateBuilder = new PredicateBuilder();
        $this->paginator = $paginator;
        $this->sanitationManager = $sanitationManager;
        $this->expressionParser = $expressionParser;
        $this->groupedCollectionConverter = new GroupedCollectionConverter($this->sanitationManager);
        $this->pagedCollectionConverter = new PagedCollectionConverter($this->sanitationManager);
        $this->pagedGroupedCollectionConverter = new PagedGroupedCollectionConverter($this->sanitationManager);
        $this->basicDataSetConverter = new DataSetCollectionConverter($this->sanitationManager);

        $this->searchEngine = new Engine(new BitapSearchProvider());
        $this->searchEngine->setSearchAttributes(array_merge(
            InternalAttributes::getInternalAttributes(),
            $config->searchableAttributes
        ));

        $this->expressionParser->setFilterGroups($this->filterRunner->getFilterManager()->getFilterGroups());
    }

    /**
     * Sets the run time context.
     *
     * @param RuntimeContext $context The run time context.
     * @return $this
     */
    public function withContext(RuntimeContext $context)
    {
        $this->runtimeContext = $context;

        return $this;
    }

    /**
     * Skips the specified amount of record when processing data.
     *
     * @param int $offset The data offset when returning results.
     * @return DataQuery
     */
    public function skip($offset)
    {
        $this->dataOffset = $offset;

        return $this;
    }

    /**
     * The name of the pages to generate, in a paged dataset.
     *
     * @param string $pageName The name of the page.
     * @return $this
     */
    public function pageBy($pageName)
    {
        $this->pageName = $pageName;
        $this->isPaged = true;

        return $this;
    }

    /**
     * Requests data be split into a collection of pages of the provided size.
     *
     * @param int $pageSize The size of pages to generate.
     * @return $this
     */
    public function limit($pageSize)
    {
        if ($pageSize === null || $pageSize === 0 || $pageSize < 0) {
            $this->dataLimit = null;

            return $this;
        }

        $this->dataLimit = $pageSize;

        return $this;
    }

    /**
     * Requests data only be returned for the provided page, in a paged result set.
     *
     * @param int $page The page to return.
     * @return $this
     */
    public function forPage($page)
    {
        $this->currentPage = $page;
        $this->isPaged = true;

        return $this;
    }

    /**
     * @param $property
     * @param $comparison
     * @param $value
     * @return $this
     */
    public function where($property, $comparison, $value)
    {
        return $this->createWrappedFilter(Where::FILTER_WHERE, [
            $property, $comparison, $value
        ]);
    }

    /**
     * Creates a filter string where the last parameter value is wrapped.
     *
     * @param string $filter The filter name.
     * @param array $parameters The filter parameters.
     * @return $this
     * @throws FilterParserException
     */
    private function createWrappedFilter($filter, $parameters)
    {
        $wrappedFilter = ExpressionParser::buildFilterArray($filter, $parameters);

        if (count($this->filters) > 0) {
            $this->filterBy($wrappedFilter);
        } else {
            $this->thenFilterBy($wrappedFilter);
        }

        return $this;
    }

    /**
     * Filters the comment collection using the provided filter.
     *
     * @param string|array $filterString The filter input.
     * @return $this
     * @throws FilterParserException
     */
    public function filterBy($filterString)
    {
        return $this->clearFilters()->thenFilterBy($filterString);
    }

    /**
     * Filters the comment collection using the provided filter.
     *
     * @param string|array $filterString The filter input.
     * @return $this
     * @throws FilterParserException
     */
    public function thenFilterBy($filterString)
    {
        if (is_array($filterString)) {
            $this->filters[] = $filterString;
        } else if (is_string($filterString)) {
            $processedFilter = $this->expressionParser->parse($filterString);

            if ($processedFilter !== null && is_array($processedFilter)) {
                foreach ($processedFilter as $filterToAdd) {
                    $this->filters[] = $filterToAdd;
                }
            }
        }

        return $this;
    }

    /**
     * Clears all registered filters.
     *
     * @return $this
     */
    public function clearFilters()
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Attempts to filter the query by the provided filter string.
     *
     * @param string $filterString The filter string.
     * @return $this
     * @throws FilterParserException
     */
    public function safeThenFilterBy($filterString)
    {
        if ($this->filterRunner->getFilterManager()->hasFilter($filterString) === false) {
            return $this;
        }

        return $this->thenFilterBy($filterString);
    }

    /**
     * Attempts to filter the query by the provided filter string.
     *
     * @param string $filterString The filter string.
     * @return $this
     * @throws FilterParserException
     */
    public function safeFilterBy($filterString)
    {
        if ($this->filterRunner->getFilterManager()->hasFilter($filterString) === false) {
            return $this;
        }


        return $this->filterBy($filterString);
    }

    /**
     * Returns the filters used while processing the query.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Constructs a "where not in" filter expression from the provided values.
     *
     * @param string $property The property name to check.
     * @param string|mixed $value The comparison value.
     * @return $this
     * @throws FilterParserException
     */
    public function whereNotIn($property, $value)
    {
        return $this->createWrappedFilter(WhereNotIn::FILTER_WHERE_NOT_IN, [
            $property, $value
        ]);
    }

    /**
     * Constructs a "where in" filter expression from the provided values.
     *
     * @param string $property The property name to check.
     * @param string|mixed $value The comparison value.
     * @return $this
     * @throws FilterParserException
     */
    public function whereIn($property, $value)
    {
        return $this->createWrappedFilter(WhereIn::FILTER_WHERE_IN, [
            $property, $value
        ]);
    }

    /**
     * Constructs a "where like" filter expression from the provided values.
     *
     * @param string $property The property name to check.
     * @param string $value The comparison string pattern.
     * @return $this
     * @throws FilterParserException
     */
    public function whereLike($property, $value)
    {
        return $this->createWrappedFilter(Like::FILTER_LIKE, [
            $property,
            $value
        ]);
    }

    /**
     * Constructs a "where not like" filter expression from the provided values.
     *
     * @param string $property The property name to check.
     * @param string|mixed $value The comparison string pattern.
     * @return $this
     * @throws FilterParserException
     */
    public function whereNotLike($property, $value)
    {
        return $this->createWrappedFilter(NotLike::FILTER_NOT_LIKE, [
            $property,
            $value
        ]);
    }

    /**
     * Sorts by the property name, ascending.
     *
     * @param string $p The property name.
     * @return DataQuery
     */
    public function sortAsc($p)
    {
        $this->sortPredicateBuilder->asc($p);

        return $this;
    }

    /**
     * Sorts by the property name, ascending.
     *
     * @param string $p The property name to sort.
     * @return $this
     */
    public function thenSortAsc($p)
    {
        $this->sortPredicateBuilder->thenByAsc($p);

        return $this;
    }

    /**
     * Sorts by the property name, descending.
     *
     * @param string $p The property name to sort.
     * @return $this
     */
    public function sortDesc($p)
    {
        $this->sortPredicateBuilder->desc($p);

        return $this;
    }

    /**
     * Sorts by the property name, descending.
     *
     * @param string $p The property name to sort.
     * @return $this
     */
    public function thenSortDesc($p)
    {
        $this->sortPredicateBuilder->thenByDesc($p);

        return $this;
    }

    /**
     * @param string $property The property to group by.
     * @param callable|null $callback An optional computed callback.
     * @return $this
     */
    public function groupBy($property, $callback = null)
    {
        $this->groupBy = $property;
        $this->groupCallback = $callback;

        if ($this->groupBy !== null) {
            $this->isGrouped = true;
        } else {
            $this->isGrouped = false;
        }

        return $this;
    }

    /**
     * Gets a string representing all sort orders and comparisons.
     *
     * @return string
     */
    public function getSortString()
    {
        return $this->sortPredicateBuilder->getSortString();
    }

    /**
     * Sets the name of an individual group's dataset.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function collectionName($name)
    {
        $this->groupCollectionName = $name;

        return $this;
    }

    /**
     * Sets the name of all dataset groups.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function nameAllGroups($name)
    {
        $this->groupCollectiveName = $name;

        return $this;
    }

    /**
     * Sets the name of an individual group.
     *
     * @param string $name The name to set.
     * @return $this
     */
    public function groupName($name)
    {
        $this->groupName = $name;

        return $this;
    }

    /**
     * Indicates that grouped results should not include empty groups.
     *
     * @return $this
     */
    public function groupWithoutEmptySets()
    {
        return $this->groupDoKeepEmptySets(false);
    }

    /**
     * Sets whether or not group results should return empty groups.
     *
     * @param bool $keepEmptySets Whether to keep empty result sets.
     * @return $this
     */
    public function groupDoKeepEmptySets($keepEmptySets)
    {
        $this->groupKeepEmptyResults = $keepEmptySets;

        return $this;
    }

    /**
     * Indicates that grouped results should include empty groups.
     *
     * @return $this
     */
    public function groupWithEmptySets()
    {
        return $this->groupDoKeepEmptySets(true);
    }

    /**
     * Sets a value indicating whether or not to gather meta data before creating pages.
     *
     * @param bool $gatherMetaData Whether to gather meta data before creating pages.
     * @return $this
     */
    public function gatherMetaDataBeforePaging($gatherMetaData)
    {
        $this->pagesGetMetaDataBeforePaging = $gatherMetaData;

        return $this;
    }

    /**
     * Sets the search terms that will be used when collecting results.
     *
     * @param string $searchTerms The text to search.
     * @return $this
     */
    public function searchFor($searchTerms)
    {
        $this->searchTerms = $searchTerms;

        return $this;
    }

    /**
     * Returns the search terms that were applied.
     *
     * @return string
     */
    public function getTerms()
    {
        if ($this->searchTerms === null) {
            return '';
        }

        return $this->searchTerms;
    }

    /**
     * Sets whether or not to automatically process comment content as Markdown.
     *
     * @param bool $useMarkdown Whether to automatically process content as Markdown.
     * @return $this
     */
    public function withMarkdown($useMarkdown)
    {
        $this->returnWithMarkdown = $useMarkdown;

        return $this;
    }

    /**
     * Retrieves the results and converts the internal dataset into its array form.
     *
     * @param CommentContract[] $sourceComments The comments to analyze.
     * @param string $repliesName The name of the nested dataset collection.
     * @return GroupedDataSetContract|PagedDataSetContract|PagedGroupedDataSetContract|DataSetContract
     * @throws FilterException|ParserException
     */
    public function getCollection($sourceComments, $repliesName)
    {
        $result = $this->get($sourceComments);

        if ($result instanceof GroupedDataSetContract) {
            if ($result instanceof PagedGroupedDataSetContract) {
                return $this->pagedGroupedCollectionConverter->covertPagedToArray($result);
            }

            return $this->groupedCollectionConverter->convertGroupedToArray($result);
        }

        if ($result instanceof PagedDataSetContract) {
            return $this->pagedCollectionConverter->convertToArray($result, $repliesName);
        }

        if ($result instanceof DataSetContract) {
            return $this->basicDataSetConverter->convertToArray($result, $repliesName);
        }

        return $result;
    }

    /**
     * Runs all filters, queries, and sorting operations and returns the dataset.
     *
     * @param CommentContract[] $data
     * @return array|PagedDataSetContract|DataSetContract|GroupedDataSetContract
     * @throws FilterException|ParserException
     */
    public function get($data)
    {
        // Filter
        if (count($this->filters) > 0 && $this->runtimeContext === null) {
            throw new FilterException('Filters cannot be executed without a run-time context. Supply a runtime context by calling withContext($context).');
        } elseif (count($this->filters) > 0 && $this->runtimeContext !== null) {

            foreach ($this->filters as $filter) {
                $filterManager = $this->filterRunner->getFilterManager();

                if (!$filterManager->hasFilter($filter[ExpressionParser::KEY_NAME])) {
                    throw new FilterException('Could not locate filter: '.$filter[ExpressionParser::KEY_NAME]);
                }
            }

            $data = $this->filterRunner->processFilters(
                $data,
                $this->filters,
                $this->runtimeContext->context,
                $this->runtimeContext->templateTagContext
            );
        }

        // Sort the results.
        $data = $this->sortPredicateBuilder->sort($data);

        if ($this->searchTerms !== null) {
            $results = $this->searchEngine->search($this->searchTerms, $data);
            $commentIdsToKeep = CommentIdRetriever::getCommentIds($results);

            $data = array_filter($data, function (CommentContract $comment) use (&$commentIdsToKeep) {
                return in_array($comment->getId(), $commentIdsToKeep);
            });
        }

        if ($this->isGrouped == false && $this->isPaged) {
            $metadataCollection = new DataSetMetadata();

            if ($this->pagesGetMetaDataBeforePaging === true) {
                $metadataCollection->setData($data);

                // Note: This method is specific to Core's implementation of the dataset metadata collection.
                $metadataCollection->processAndUnset();
            }

            $paginatedDataset = $this->paginator->paginate(
                $data,
                $this->pageName,
                $this->currentPage,
                $this->dataOffset,
                $this->dataLimit
            );

            $paginatedDataset->setDatasetMetadata($metadataCollection);

            return $paginatedDataset;
        }

        if ($this->isGrouped) {
            $dataGroup = new DataGroupBuilder($this->paginator);
            $dataGroup->setCallback($this->groupCallback);
            $dataGroup->setProperty($this->groupBy);

            $dataGroup->withMarkdown($this->returnWithMarkdown);

            return $dataGroup->setCollectionName($this->groupCollectionName)
                ->doKeepEmptyGroups($this->groupKeepEmptyResults)
                ->setIndividualGroupName($this->groupName)->setCollectiveGroupName($this->groupCollectiveName)
                ->skip($this->dataOffset)->limit($this->dataLimit)
                ->pageBy($this->pageName)->forPage($this->currentPage)
                ->gatherMetadataBeforePaging($this->pagesGetMetaDataBeforePaging)
                ->paginateResults($this->isPaged)->group($data);
        }

        // Process non-paged limits and offsets.
        if ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit == null) {
            $data = array_slice($data, $this->dataOffset, null, true);
        } elseif ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit !== null && $this->dataLimit > 0) {
            $data = array_slice($data, $this->dataOffset, $this->dataLimit, true);
        } elseif (($this->dataOffset === null || $this->dataOffset === 0) && $this->dataLimit !== null && $this->dataLimit > 0) {
            $data = array_slice($data, 0, $this->dataLimit, true);
        }

        // Create a non-paged/non-grouped dataset.
        $dataSet = new DataSet();

        // Process automatic Markdown at this stage since we will have to invoke it less if there were filters, ec.
        if ($this->returnWithMarkdown && MarkdownParserFactory::hasInstance()) {
            /** @var CommentContract $comment */
            foreach ($data as &$comment) {
                $parsedContent = trim(MarkdownParserFactory::$instance->parse($comment->getRawContent()));

                $comment->setDataAttribute(CommentContract::KEY_CONTENT, $parsedContent);
                $comment->setDataAttribute(CommentContract::KEY_COMMENT_MARKDOWN, $parsedContent);
            }
        }

        $dataSet->setData($data);

        return $dataSet;
    }

    /**
     * Indicates if soft deleted comments should be part of the result set.
     *
     * @param bool $trashed If false, soft deleted comments will be removed.
     * @return $this
     * @throws FilterParserException
     */
    public function withTrashed($trashed = false)
    {
        $filterToApply = 'is:deleted(false)';

        if ($trashed === true) {
            $filterToApply = null;
        }

        if ($filterToApply !== null) {
            if (count($this->filters) > 0) {
                return $this->thenFilterBy($filterToApply);
            } else {
                return $this->filterBy($filterToApply);
            }
        }

        return $this;
    }

}
