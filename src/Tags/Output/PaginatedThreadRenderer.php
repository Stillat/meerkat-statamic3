<?php

namespace Stillat\Meerkat\Tags\Output;

use Illuminate\Support\Collection;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

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

    /**
     * @param Collection $collection
     * @param $pageName
     * @param $offset
     * @param $limit
     * @return array
     */
    public static function renderPaginatedThread($collection, $pageName, $offset, $limit)
    {
        if ($limit === null || $limit === 0) {
            $limit = $collection->count();
        }

        $limit = (int)$limit;

        $totalResults = $collection->count();
        $currentPage = (int)request()->get($pageName);
        $currentOffset = (($currentPage - 1) * $limit) + $offset;

        $displayItems = $collection->slice($currentOffset, $limit);
        $itemsCount = $totalResults - $offset;
        $lastPage = (int)ceil($itemsCount / $limit);


        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        } elseif ($currentPage < 1) {
            $currentPage = 1;
        }

        $paginator = new LengthAwarePaginator($displayItems, $itemsCount, $limit, $currentPage);
        $paginator->setPageName($pageName);
        $paginator->setPath(url()->current());
        $paginator->appends(request()->all());

        $paginationMeta = [
          self::KEY_META_TOTAL_ITEMS => $itemsCount,
          self::KEY_META_ITEMS_PER_PAGE => $limit,
          self::KEY_META_TOTAL_PAGES => $paginator->lastPage(),
          self::KEY_META_CURRENT_PAGE => $paginator->currentPage(),
          self::KEY_META_PREV_PAGE => $paginator->previousPageUrl(),
          self::KEY_META_NEXT_PAGE => $paginator->nextPageUrl(),
          self::KEY_META_AUTO_LINKS => $paginator->render(),
          self::KEY_META_LINKS => $paginator->renderArray() // NOTE: Specific to Statamic's LengthAwarePaginator.
        ];

        $paginatedCollection = $paginator->getCollection();

        return [
            self::KEY_RETURN_DATA => $paginatedCollection->all(),
            self::KEY_RETURN_META => $paginationMeta,
            self::KEY_TOTAL_RESULTS => $totalResults
        ];
    }

}