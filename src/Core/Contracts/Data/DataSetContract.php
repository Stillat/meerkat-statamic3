<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

use Iterator;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Interface DataSetContract
 *
 * Represents a basic Core dataset.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data
 * @since 2.0.0
 */
interface DataSetContract extends MetadataCollectionContract, Iterator
{

    /**
     * Returns the dataset and any additional meta data properties.
     *
     * @return array
     */
    public function toArray();

    /**
     * Sets the dataset's raw data.
     *
     * @param array $data The raw data.
     * @return void
     */
    public function setData($data);

    public function getData();

    /**
     * Flattens the dataset into one-dimensional array.
     *
     * @return CommentContract[]
     */
    public function flattenDataset();

    /**
     * Returns the total number of results in the expanded dataset.
     *
     * @return int
     */
    public function count();

}
