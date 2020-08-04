<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Core\Data\Filters\FilterRunner;
use Stillat\Meerkat\Core\Exceptions\FilterException;

class DataQuery
{

    protected $filters = [];
    /**
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
    private $paginator = null;
    private $isPaged = false;
    private $isGrouped = false;
    private $pageName = 'page';
    private $currentPage = 0;
    private $dataOffset = 0;
    private $dataLimit = null;

    public function __construct(PaginatorContract $paginator, FilterRunner $filterRunner)
    {
        $this->filterRunner = $filterRunner;
        $this->sortPredicateBuilder = new PredicateBuilder();
        $this->paginator = $paginator;
    }

    /**
     * @param RuntimeContext $context
     * @return $this
     */
    public function withContext(RuntimeContext $context)
    {
        $this->runtimeContext = $context;

        return $this;
    }

    /**
     * @param int $offset The data offset when returning results.
     * @return DataQuery
     */
    public function skip($offset)
    {
        $this->dataOffset = $offset;

        return $this;
    }

    /**
     * @param $pageName
     * @return $this
     */
    public function pageBy($pageName)
    {
        $this->pageName = $pageName;
        $this->isPaged = true;

        return $this;
    }

    /**
     * @param $pageSize
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
     * @param $page
     * @return $this
     */
    public function forPage($page)
    {
        $this->currentPage = $page;
        $this->isPaged = true;

        return $this;
    }

    /**
     * @param $filterString
     */
    public function filterBy($filterString)
    {
        $this->filters = [];

        return $this->thenFilterBy($filterString);
    }

    /**
     * @param $filterString
     * @return $this
     */
    public function thenFilterBy($filterString)
    {
        $this->filters[] = $filterString;

        return $this;
    }


    /**
     * @param string $p The property name.
     * @return DataQuery
     */
    public function sortAsc($p)
    {
        $this->sortPredicateBuilder->asc($p);

        return $this;
    }

    /**
     * @param $p
     * @return $this
     */
    public function thenSortAsc($p)
    {
        $this->sortPredicateBuilder->thenByAsc($p);

        return $this;
    }

    /**
     * @param $p
     * @return $this
     */
    public function sortDesc($p)
    {
        $this->sortPredicateBuilder->desc($p);

        return $this;
    }

    /**
     * @param $p
     * @return $this
     */
    public function thenSortDesc($p)
    {
        $this->sortPredicateBuilder->thenByDesc($p);

        return $this;
    }

    /**
     * @param CommentContract[] $data
     * @return array|PagedDataSetContract|DataSetContract|GroupedDataSetContract
     * @throws FilterException
     */
    public function get($data)
    {
        // Filter
        if (count($this->filters) > 0 && $this->runtimeContext === null) {
            throw new FilterException('Filters cannot be executed within a run-time context. Supply a runtime context by calling withContext($context).');
        } elseif (count($this->filters) > 0 && $this->runtimeContext !== null) {
            $data = $this->filterRunner->processFilters(
                $data,
                $this->runtimeContext->parameters,
                implode('|', $this->filters),
                $this->runtimeContext->context,
                $this->runtimeContext->templateTagContext
            );
        }

        // Sort the results.
        $data = $this->sortPredicateBuilder->sort($data);

        if ($this->isGrouped == false && $this->isPaged) {
            return $this->paginator->paginate(
                $data,
                $this->pageName,
                $this->currentPage,
                $this->dataOffset,
                $this->dataLimit
            );
        }

        if ($this->isGrouped == true && $this->isPaged) {
            // TODO: Implement.
        }

        if ($this->isGrouped == true && $this->isPaged == false) {
            // TODO: Implement.
        }

        // Process non-paged limits and offsets.
        if ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit == null) {
            $data = array_slice($data, $this->dataOffset, null, true);
        } elseif ($this->dataOffset !== null && $this->dataOffset > 0 && $this->dataLimit !== null && $this->dataLimit > 0) {
            $data = array_slice($data, $this->dataOffset, $this->dataLimit, true);
        } elseif (($this->dataOffset === null || $this->dataOffset === 0) && $this->dataLimit !== null && $this->dataLimit > 0) {
            $data = array_slice($data, 0, $this->dataLimit, true);
        }

        return $data;
    }

}