<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Data\Concerns\ContainsGroups;
use Stillat\Meerkat\Core\Data\Concerns\GetsAssociatedDatasetData;
use Stillat\Meerkat\Core\Data\Concerns\IteratesDataSets;
use Stillat\Meerkat\Core\Data\Concerns\ManagesGroupMetaData;
use Stillat\Meerkat\Core\Data\Helpers\GroupFlattener;

/**
 * Class GroupedDataSet
 *
 * Represents a dataset containing groups.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class GroupedDataSet implements GroupedDataSetContract
{
    use ContainsGroups, ManagesGroupMetaData, IteratesDataSets, GetsAssociatedDatasetData;

    /**
     * @var array
     */
    protected $data = [];

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
     * Flattens the dataset into one-dimensional array.
     *
     * @return array
     */
    public function flattenDataset()
    {
        if ($this->flattenedData === null) {
            $this->flattenedData = GroupFlattener::flatten(
                $this->data,
                $this->getCollectiveGroupName(),
                $this->getGroupName(),
                $this->getGroupDatasetName()
            );
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
        return count($this->flattenDataset());
    }
}
