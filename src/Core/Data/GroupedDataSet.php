<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Data\Concerns\ContainsGroups;
use Stillat\Meerkat\Core\Data\Concerns\GetsAssociatedDatasetData;
use Stillat\Meerkat\Core\Data\Concerns\IteratesDataSets;
use Stillat\Meerkat\Core\Data\Concerns\ManagesGroupMetaData;
use Stillat\Meerkat\Core\Data\Helpers\GroupFlattener;
use Stillat\Meerkat\Core\Data\Helpers\GroupMapper;

/**
 * Class GroupedDataSet
 *
 * Represents a dataset containing groups.
 *
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
     * @param  array  $data The raw data.
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Applies the mutation callback to all group items.
     *
     * @param  callable  $callback The function to execute against all group items.
     */
    public function mutate($callback)
    {
        $this->data = GroupMapper::mutate($this->data,
            $this->getCollectiveGroupName(),
            $this->getGroupName(),
            $this->getGroupDatasetName(), $callback);
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
}
