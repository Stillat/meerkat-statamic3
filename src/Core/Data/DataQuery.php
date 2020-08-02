<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;

class DataQuery
{

    /**
     * The sort predicate builder.
     *
     * @var PredicateBuilder
     */
    private $sortPredicateBuilder = null;

    private $paginator = null;

    private $isPaged = false;

    private $pageName = 'page';

    private $currentPage = 0;

    private $dataOffset = 0;
    private $dataLimit = null;

    public function __construct(PaginatorContract $paginator)
    {
        $this->sortPredicateBuilder = new PredicateBuilder();
        $this->paginator = $paginator;
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

    public function pageBy($pageName)
    {
        $this->pageName = $pageName;
        $this->isPaged = true;

        return $this;
    }

    public function limit($pageSize)
    {
        if ($pageSize === null || $pageSize === 0 || $pageSize < 0) {
            $this->dataLimit = null;

            return $this;
        }

        $this->dataLimit = $pageSize;

        return $this;
    }

    public function forPage($page)
    {
        $this->currentPage = $page;
        $this->isPaged = true;

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

    public function thenSortAsc($p)
    {
        $this->sortPredicateBuilder->thenByAsc($p);

        return $this;
    }

    public function sortDesc($p)
    {
        $this->sortPredicateBuilder->desc($p);

        return $this;
    }

    public function thenSortDesc($p)
    {
        $this->sortPredicateBuilder->thenByDesc($p);

        return $this;
    }

    public function get($data)
    {
        // Filter

        // Sort the results.
        $data = $this->sortPredicateBuilder->sort($data);

        if ($this->isPaged) {
            return $this->paginator->paginate(
                $data,
                $this->pageName,
                $this->currentPage,
                $this->dataOffset,
                $this->dataLimit
            );
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