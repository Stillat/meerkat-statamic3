<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface PaginatorContract
 *
 * Provides a consistent API for generating paged data sets from Core.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data
 * @since 2.0.0
 */
interface PaginatorContract
{

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
    public function paginate($collection, $pageName, $currentPage, $offset, $limit);

}
