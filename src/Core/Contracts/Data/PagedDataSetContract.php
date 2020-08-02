<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface PagedDataSetContract
 *
 * Represents a paged data set.
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

}
