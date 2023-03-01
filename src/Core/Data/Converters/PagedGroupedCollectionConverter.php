<?php

namespace Stillat\Meerkat\Core\Data\Converters;

use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;

/**
 * Class PagedGroupedCollectionConverter
 *
 * Provides utilities for converting a PagedGroupedDataSet into an array.
 *
 * @since 2.0.0
 */
class PagedGroupedCollectionConverter extends GroupedCollectionConverter
{
    /**
     * Converts a PagedGroupedDataSet into its array form.
     *
     * @param  PagedGroupedDataSetContract  $dataset The dataset to convert.
     * @return PagedGroupedDataSetContract
     */
    public function covertPagedToArray(PagedGroupedDataSetContract $dataset)
    {
        $data = $this->convertGroupedToArray($dataset);
        $dataset->setData($data->getData());

        return $dataset;
    }
}
