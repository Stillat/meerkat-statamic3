<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\MetadataCollectionContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Data\Concerns\GetsAssociatedDatasetData;
use Stillat\Meerkat\Core\Data\Concerns\IteratesDataSets;

/**
 * Class PagedDataSet
 *
 * Represents a collection of paged data.
 *
 * @since 2.0.0
 */
class PagedDataSet implements PagedDataSetContract
{
    use IteratesDataSets, GetsAssociatedDatasetData;

    const KEY_PAGINATE = 'paginate';

    const KEY_RETURN_META = 'meta';

    const KEY_RETURN_DATA = 'data';

    const KEY_TOTAL_RESULTS = 'total_results';

    const KEY_META_TOTAL_ITEMS = 'total_items';

    const KEY_META_ITEMS_PER_PAGE = 'items_per_page';

    const KEY_META_TOTAL_PAGES = 'total_pages';

    const KEY_META_CURRENT_PAGE = 'current_page';

    const KEY_META_PREV_PAGE = 'prev_page';

    const KEY_META_NEXT_PAGE = 'next_page';

    const KEY_META_AUTO_LINKS = 'auto_links';

    const KEY_META_LINKS = 'links';

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
     * A collection of additional meta data.
     *
     * @var array
     */
    protected $additionalMeta = [];

    /**
     * The metadata collection instance, if any.
     *
     * @var MetadataCollectionContract|null
     */
    protected $datasetMetadata = null;

    /**
     * Returns a collection of data attributes representing the paged data.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_RETURN_META => $this->getMetaData(),
            self::KEY_RETURN_DATA => $this->getPageData(),
            self::KEY_TOTAL_RESULTS => $this->totalResults,
        ];
    }

    /**
     * Returns a collection of meta data describing the paged data.
     *
     * @return array
     */
    public function getMetaData()
    {
        $baseMetaData = [
            self::KEY_META_TOTAL_ITEMS => $this->itemsCount,
            self::KEY_META_ITEMS_PER_PAGE => $this->limit,
        ];

        return array_merge($baseMetaData, $this->additionalMeta);
    }

    /**
     * Returns the data items to display on the current page.
     *
     * @return array
     */
    public function getPageData()
    {
        return $this->displayItems;
    }

    /**
     * Sets the dataset's raw data.
     *
     * @param  array  $data The raw data.
     * @return void
     */
    public function setData($data)
    {
        $this->displayItems = $data;
    }

    /**
     * Gets the underlying comment dataset.
     *
     * @return CommentContract[]
     */
    public function getData()
    {
        return $this->displayItems;
    }

    /**
     * Gets the maximum number of records per page.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the maximum number of records per page.
     *
     * @param  int  $limit The limit.
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Gets the total number of items in the collection.
     *
     * @return int
     */
    public function getTotalResults()
    {
        return $this->totalResults;
    }

    /**
     * Sets the total number of items in the collection.
     *
     * @param  int  $totalResults The total number of items.
     */
    public function setTotalResults($totalResults)
    {
        $this->totalResults = $totalResults;
    }

    /**
     * Gets the current data offset.
     *
     * @return int
     */
    public function getCurrentOffset()
    {
        return $this->currentOffset;
    }

    /**
     * Sets where to start in the list of ordered data.
     *
     * @param  int  $currentOffset The offset.
     */
    public function setCurrentOffset($currentOffset)
    {
        $this->currentOffset = $currentOffset;
    }

    /**
     * Gets the last page's number.
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        return $this->lastPageNumber;
    }

    /**
     * Sets the last page's number.
     *
     * @param  int  $lastPageNumber The page number.
     */
    public function setLastPageNumber($lastPageNumber)
    {
        $this->lastPageNumber = $lastPageNumber;
    }

    /**
     * Gets any additional paged meta data.
     *
     * @return array
     */
    public function getAdditionalMetaData()
    {
        return $this->additionalMeta;
    }

    /**
     * Gets the current page number.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Sets the current page number.
     *
     * @param  int  $currentPage The current page number.
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * Gets the total number of items, without the offset.
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * Sets the total number of items, without the offset.
     *
     * @param  int  $itemsCount The items count.
     */
    public function setItemsCount($itemsCount)
    {
        $this->itemsCount = $itemsCount;
    }

    /**
     * Copies the values from the provided dataset to the current instance.
     *
     * @param  PagedDataSetContract  $result The dataset to copy data from.
     */
    public function fromPaginatorResult(PagedDataSetContract $result)
    {
        $this->setLimit($result->getLimit());
        $this->setTotalResults($result->getTotalResults());
        $this->setCurrentPage($result->getCurrentPage());
        $this->setCurrentOffset($result->getCurrentOffset());
        $this->setDisplayItems($result->getDisplayItems());
        $this->setItemsCount($result->getItemsCount());
        $this->setLastPageNumber($result->getLastPageNumber());
        $this->setAdditionalMetaData($result->getAdditionalMetaData());
    }

    /**
     * Sets additional meta data for the paged result.
     *
     * @param  array  $metaData The meta data.
     */
    public function setAdditionalMetaData($metaData)
    {
        $this->additionalMeta = $metaData;
    }

    /**
     * Flattens the dataset into one-dimensional array.
     *
     * @return array
     */
    public function flattenDataset()
    {
        if ($this->flattenedData === null) {
            $this->flattenedData = $this->getDisplayItems();
        }

        return $this->flattenedData;
    }

    /**
     * Gets the values for the current page.
     *
     * @return array
     */
    public function getDisplayItems()
    {
        return $this->displayItems;
    }

    /**
     * Sets the items that should be displayed on the current page.
     *
     * @param  array  $displayItems The current page's items.
     */
    public function setDisplayItems($displayItems)
    {
        $this->displayItems = $displayItems;
    }

    /**
     * Gets the metadata collection, if any.
     *
     * @return MetadataCollectionContract|null
     */
    public function getDatasetMetadata()
    {
        return $this->datasetMetadata;
    }

    /**
     * Sets the metadata collection.
     *
     * @param  MetadataCollectionContract  $metadataCollection The metadata collection.
     * @return void
     */
    public function setDatasetMetadata(MetadataCollectionContract $metadataCollection)
    {
        $this->datasetMetadata = $metadataCollection;
    }

    /**
     * Returns the total number of results in the expanded dataset.
     *
     * @return int
     */
    public function count()
    {
        return count($this->displayItems);
    }
}
