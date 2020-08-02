<?php

namespace Stillat\Meerkat\Data;

use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Stillat\Meerkat\Core\Data\PaginatorBase;
use Stillat\Meerkat\Core\Data\PaginationResult;

/**
 * Class Paginator
 *
 * Uses the Statamic LengthAwarePaginator paginator to provide additional page meta data.
 *
 * @package Stillat\Meerkat\Data
 * @since 2.0.0
 */
class Paginator extends PaginatorBase
{

    /**
     * Generates a collection of additional page meta data.
     *
     * @return array
     */
    protected function getMetaData()
    {
        $paginator = new LengthAwarePaginator($this->displayItems, $this->itemsCount, $this->limit, $this->currentPage);
        $paginator->setPageName($this->pageName);
        $paginator->setPath(url()->current());
        $paginator->appends(request()->all());

        return [
            PaginationResult::KEY_META_TOTAL_PAGES => $paginator->lastPage(),
            PaginationResult::KEY_META_CURRENT_PAGE => $paginator->currentPage(),
            PaginationResult::KEY_META_PREV_PAGE => $paginator->previousPageUrl(),
            PaginationResult::KEY_META_NEXT_PAGE => $paginator->nextPageUrl(),
            PaginationResult::KEY_META_AUTO_LINKS => $paginator->render(),
            PaginationResult::KEY_META_LINKS => $paginator->renderArray()
        ];
    }

}
