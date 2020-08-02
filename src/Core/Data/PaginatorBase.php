<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;

/**
 * Class PaginatorBase
 *
 * Provides a base implementation for data paginators.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
abstract class PaginatorBase implements PaginatorContract
{

    /**
     * The maximum number of records per page.
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * The total number of items in the collection.
     *
     * @var int
     */
    protected $totalResults = 0;

    /**
     * The current data page.
     *
     * @var int
     */
    protected $currentPage = 0;

    /**
     * Where to start in the list of data.
     *
     * @var int
     */
    protected $currentOffset = 0;

    /**
     * The items to include in the result set.
     *
     * @var array
     */
    protected $displayItems = [];

    /**
     * The amount of total data items, without the offset.
     *
     * @var int
     */
    protected $itemsCount = 0;

    /**
     * The page number of the last data page.
     *
     * @var int
     */
    protected $lastPageNumber = 0;

    /**
     * The name of the page collection.
     *
     * @var string
     */
    protected $pageName = 'page';

    /**
     * Creates a paged data set for the provided data and constraints.
     *
     * @param array $collection The data to page.
     * @param string $pageName The name of the pages to create.
     * @param int $currentPage The current data page.
     * @param int $offset Where to start in the data set.
     * @param int $limit The maximum number of records per page.
     * @return PagedDataSetContract
     */
    public function paginate($collection, $pageName, $currentPage, $offset, $limit)
    {
        $this->pageName = $pageName;

        $this->preparePagedData($collection, $pageName, $currentPage, $offset, $limit);

        return $this->getResult();
    }

    /**
     * Creates a paginator result and returns it.
     *
     * @return PaginationResult
     */
    protected function getResult()
    {
        $result = new PaginationResult();

        $result->limit = $this->limit;
        $result->totalResults = $this->totalResults;
        $result->currentPage = $this->currentPage;
        $result->currentOffset = $this->currentOffset;
        $result->displayItems = $this->displayItems;
        $result->itemsCount = $this->itemsCount;
        $result->lastPageNumber = $this->lastPageNumber;

        $result->additionalMeta = $this->getMetaData();

        return $result;
    }

    /**
     * Generates a collection of additional page meta data.
     *
     * @return array
     */
    abstract protected function getMetaData();

    /**
     * Prepares the data collection for paging.
     *
     * @param array $collection The data to page.
     * @param string $pageName The name of the pages to create.
     * @param int $currentPage The current data page.
     * @param int $offset Where to start in the data set.
     * @param int $limit The maximum number of records per page.
     * @return void
     */
    protected function preparePagedData($collection, $pageName, $currentPage, $offset, $limit)
    {
        if ($collection === null || !is_array($collection)) {
            $collection = [];
        }

        if ($limit === null || $limit === 0) {
            $limit = count($collection);
        }

        $limit = (int)$limit;
        $totalResults = count($collection);
        $currentOffset = (($currentPage - 1) * $limit) + $offset;

        $displayItems = array_slice($collection, $currentOffset, $limit, true);
        $itemsCount = $totalResults - $offset;
        $lastPage = (int)ceil($itemsCount / $limit);

        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        } elseif ($currentPage < 1) {
            $currentPage = 1;
        }

        $this->limit = $limit;
        $this->totalResults = $totalResults;
        $this->displayItems = $displayItems;
        $this->currentOffset = $currentOffset;
        $this->currentPage = $currentPage;
        $this->lastPageNumber = $lastPage;
        $this->itemsCount = $itemsCount;
    }

}
