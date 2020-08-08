<?php

namespace Stillat\Meerkat\Tags\Output;

use Illuminate\Support\Collection;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Core\Data\PagedDataSet;

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
            $collectionName => $paginateData[PagedDataSet::KEY_RETURN_DATA],
            PagedDataSet::KEY_PAGINATE => $paginateData[PagedDataSet::KEY_RETURN_META],
            PagedDataSet::KEY_TOTAL_RESULTS => $paginateData[PagedDataSet::KEY_TOTAL_RESULTS]
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
