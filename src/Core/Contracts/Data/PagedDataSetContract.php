<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface PagedDataSetContract
 *
 * Represents a paged dataset.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data
 * @since 2.0.0
 */
interface PagedDataSetContract extends DataSetContract
{

    /**
     * Generates a collection of additional meta data properties.
     *
     * @return array
     */
    public function getMetaData();

    /**
     * Returns the page data.
     *
     * @return array
     */
    public function getPageData();

    /**
     * Sets the maximum number of records per page.
     *
     * @param int $limit The limit.
     */
    public function setLimit($limit);

    /**
     * Gets the maximum number of records per page.
     *
     * @return int
     */
    public function getLimit();

    /**
     * Sets the total number of items in the collection.
     *
     * @param int $totalResults The total number of items.
     */
    public function setTotalResults($totalResults);

    /**
     * Gets the total number of items in the collection.
     *
     * @return int
     */
    public function getTotalResults();

    /**
     * Sets where to start in the list of ordered data.
     *
     * @param int $currentOffset The offset.
     */
    public function setCurrentOffset($currentOffset);

    /**
     * Gets the current data offset.
     *
     * @return int
     */
    public function getCurrentOffset();

    /**
     * Gets the values for the current page.
     *
     * @return array
     */
    public function getDisplayItems();

    /**
     * Sets the items that should be displayed on the current page.
     *
     * @param array $displayItems The current page's items.
     */
    public function setDisplayItems($displayItems);

    /**
     * Gets the last page's number.
     *
     * @return int
     */
    public function getLastPageNumber();

    /**
     * Sets the last page's number.
     *
     * @param int $lastPageNumber The page number.
     */
    public function setLastPageNumber($lastPageNumber);

    /**
     * Sets additional meta data for the paged result.
     *
     * @param array $metaData The meta data.
     */
    public function setAdditionalMetaData($metaData);

    /**
     * Gets any additional paged meta data.
     *
     * @return array
     */
    public function getAdditionalMetaData();

    /**
     * Sets the current page number.
     *
     * @param int $currentPage The current page number.
     */
    public function setCurrentPage($currentPage);

    /**
     * Gets the current page number.
     *
     * @return int
     */
    public function getCurrentPage();

    /**
     * Sets the total number of items, without the offset.
     *
     * @param int $itemsCount The items count.
     */
    public function setItemsCount($itemsCount);

    /**
     * Gets the total number of items, without the offset.
     *
     * @return int
     */
    public function getItemsCount();

    /**
     * Sets the metadata collection.
     *
     * @param MetadataCollectionContract $metadataCollection The metadata collection.
     * @return void
     */
    public function setDatasetMetadata(MetadataCollectionContract $metadataCollection);

    /**
     * Gets the metadata collection, if any.
     *
     * @return MetadataCollectionContract|null
     */
    public function getDatasetMetadata();

}
