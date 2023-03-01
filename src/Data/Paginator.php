<?php

namespace Stillat\Meerkat\Data;

use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Stillat\Meerkat\Core\Data\PagedDataSet;
use Stillat\Meerkat\Core\Data\PaginatorBase;

/**
 * Class Paginator
 *
 * Uses the Statamic LengthAwarePaginator paginator to provide additional page meta data.
 *
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
            PagedDataSet::KEY_META_TOTAL_PAGES => $paginator->lastPage(),
            PagedDataSet::KEY_META_CURRENT_PAGE => $paginator->currentPage(),
            PagedDataSet::KEY_META_PREV_PAGE => $paginator->previousPageUrl(),
            PagedDataSet::KEY_META_NEXT_PAGE => $paginator->nextPageUrl(),
            PagedDataSet::KEY_META_AUTO_LINKS => $paginator->render(),
            PagedDataSet::KEY_META_LINKS => $paginator->renderArray(),
        ];
    }
}
