<?php

namespace Stillat\Meerkat\Core\Data\Converters;

use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;

/**
 * Class DataSetCollectionConverter
 *
 * Provides utilities for converting a DataSet into an array.
 *
 * @since 2.0.0
 */
class DataSetCollectionConverter extends BaseCollectionConverter
{
    /**
     * Converts the DataSet to its array form.
     *
     * @param  DataSetContract  $dataset The dataset to convert.
     * @param  string  $datasetName The dataset name.
     * @return DataSetContract
     */
    public function convertToArray(DataSetContract $dataset, $datasetName)
    {
        $data = $dataset->getData();
        $data = $this->convert($data, $datasetName);
        $dataset->setData($data);

        return $dataset;
    }
}
