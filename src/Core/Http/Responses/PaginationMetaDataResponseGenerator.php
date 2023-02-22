<?php

namespace Stillat\Meerkat\Core\Http\Responses;

use Stillat\Meerkat\Core\Data\PagedDataSet;

/**
 * Class PaginationMetaDataResponseGenerator
 *
 * Provides utilities for converting pagination meta data to an API response.
 *
 * @since 2.0.0
 */
class PaginationMetaDataResponseGenerator
{
    const KEY_API_CURRENT_PAGE = 'current_page';

    const KEY_API_TOTAL_ITEMS = 'total_items';

    const KEY_API_TOTAL_PAGES = 'total_pages';

    const KEY_API_ITEMS_PER_PAGE = 'items_per_page';

    /**
     * Converts the provided meta data into an appropriate API response.
     *
     * @param  array  $metaData The pagination meta data.
     * @return array
     */
    public static function getApiResponse($metaData)
    {
        return [
            PaginationMetaDataResponseGenerator::KEY_API_TOTAL_PAGES => $metaData[PagedDataSet::KEY_META_TOTAL_PAGES],
            PaginationMetaDataResponseGenerator::KEY_API_CURRENT_PAGE => $metaData[PagedDataSet::KEY_META_CURRENT_PAGE],
            PaginationMetaDataResponseGenerator::KEY_API_TOTAL_ITEMS => $metaData[PagedDataSet::KEY_META_TOTAL_ITEMS],
            PaginationMetaDataResponseGenerator::KEY_API_ITEMS_PER_PAGE => $metaData[PagedDataSet::KEY_META_ITEMS_PER_PAGE],
        ];
    }
}
