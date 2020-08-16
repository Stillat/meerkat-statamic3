<?php

namespace Stillat\Meerkat\Core\Data;

use ArrayAccess;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Data\Concerns\GetsAssociatedDatasetData;
use Stillat\Meerkat\Core\Data\Concerns\IteratesDataSets;

/**
 * Class DataSet
 *
 * Represents a basic comment dataset.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class DataSet implements DataSetContract, ArrayAccess
{
    use IteratesDataSets, GetsAssociatedDatasetData;

    /**
     * The underlying data of the dataset.
     *
     * @var array
     */
    public $data = [];

    /**
     * Returns the dataset and any additional meta data properties.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the dataset's raw data.
     *
     * @param array $data The raw data.
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Returns the value at the specified offset.
     *
     * @param mixed $offset The offset to get.
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * Tests whether or not an offset exists.
     *
     * @param mixed $offset An offset to check for.
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Flattens the dataset into one-dimensional array.
     *
     * @return array
     */
    public function flattenDataset()
    {
        if ($this->flattenedData === null) {
            $this->flattenedData = array_values($this->data);
        }

        return $this->flattenedData;
    }

    /**
     * Returns the total number of results in the expanded dataset.
     *
     * @return int
     */
    public function count()
    {
        if ($this->data !== null && is_array($this->data)) {
            return count($this->data);
        }

        return 0;
    }

}
