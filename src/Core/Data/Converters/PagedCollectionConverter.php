<?php

namespace Stillat\Meerkat\Core\Data\Converters;

use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;

/**
 * Class PagedCollectionConverter
 *
 * Provides utilities for converting paginated datasets into their array form.
 *
 * @package Stillat\Meerkat\Core\Data\Converters
 * @since 2.0.0
 */
class PagedCollectionConverter extends BaseCollectionConverter
{

    /**
     * Converts a paginated dataset into it's array form.
     *
     * @param PagedDataSetContract $dataset The dataset to convert.
     * @param string $datasetName The collection name.
     * @return PagedDataSetContract
     */
    public function convertToArray(PagedDataSetContract $dataset, $datasetName)
    {
        $data = $dataset->getData();
        $data = $this->convert($data, $datasetName);
        $dataset->setData($data);

        return $dataset;
    }

}
