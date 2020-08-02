<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;

/**
 * Class PaginationResult
 *
 * Represents a collection of paged data.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class PaginationResult implements PagedDataSetContract
{

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
    public $limit = 0;

    /**
     * The total number of items in the collection.
     *
     * @var int
     */
    public $totalResults = 0;

    /**
     * The current data page.
     *
     * @var int
     */
    public $currentPage = 0;

    /**
     * Where to start in the list of data.
     *
     * @var int
     */
    public $currentOffset = 0;

    /**
     * The items to include in the result set.
     *
     * @var array
     */
    public $displayItems = [];

    /**
     * The amount of total data items, without the offset.
     *
     * @var int
     */
    public $itemsCount = 0;

    /**
     * The page number of the last data page.
     *
     * @var int
     */
    public $lastPageNumber = 0;

    /**
     * A collection of additional meta data.
     *
     * @var array
     */
    public $additionalMeta = [];

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
          self::KEY_TOTAL_RESULTS => $this->totalResults
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
            self::KEY_META_ITEMS_PER_PAGE => $this->limit
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

}
