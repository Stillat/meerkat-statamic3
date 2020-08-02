<?php

namespace Stillat\Meerkat\Tags\Output;

use Illuminate\Support\Collection;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;

/**
 * Class PaginatedThreadRenderer
 *
 * Provides utilities for managing paginated Meerkat threads.
 *
 * @package Stillat\Meerkat\Tags\Output
 * @since 2.0.0
 */
class PaginatedThreadRenderer
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

    private $paginator = null;

    public function __construct(PaginatorContract $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Generates a collection of data that can be used to render paginated comment threads.
     *
     * @param $collectionName
     * @param Collection $collection The comment collection.
     * @param string $pageName The name of the URL parameter to use when generating links.
     * @param int $offset Where to start in the list of comments.
     * @param int $limit Maximum number of comments per page.
     * @return array
     */
    public function preparePaginatedThread($collectionName, $collection, $pageName, $offset, $limit)
    {
        $paginateData = self::getPaginationData($collection, $pageName, $offset, $limit);

        return [
            $collectionName => $paginateData[self::KEY_RETURN_DATA],
            self::KEY_PAGINATE => $paginateData[self::KEY_RETURN_META],
            self::KEY_TOTAL_RESULTS => $paginateData[self::KEY_TOTAL_RESULTS]
        ];
    }

    /**
     * Produces a limited collection of comments and meta data.
     *
     * @param Collection $collection
     * @param string $pageName The name of the URL parameter to use when generating links.
     * @param int $offset Where to start in the list of comments.
     * @param int $limit Maximum number of comments per page.
     * @return array
     */
    public function getPaginationData($collection, $pageName, $offset, $limit)
    {
        return $this->paginator->paginate(
            $collection->all(),
            $pageName,
            (int)request()->get($pageName),
            $offset,
            $limit
        )->toArray();
    }

}
