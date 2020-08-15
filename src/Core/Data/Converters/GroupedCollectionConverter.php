<?php

namespace Stillat\Meerkat\Core\Data\Converters;

use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;

/**
 * Class GroupedCollectionConverter
 *
 * Provides utilities for converting grouped collections to arrays.
 *
 * @package Stillat\Meerkat\Core\Data\Converters
 * @since 2.0.0
 */
class GroupedCollectionConverter extends BaseCollectionConverter
{

    /**
     * Converts a grouped collection to its array form.
     *
     * @param GroupedDataSetContract $dataset The dataset to convert.
     * @return GroupedDataSetContract
     */
    public function convertGroupedToArray(GroupedDataSetContract $dataset)
    {
        $data = $dataset->getData();
        $groups = $data[$dataset->getCollectiveGroupName()];
        $datasetName = $dataset->getGroupDatasetName();

        foreach ($groups as $groupName => &$groupValues) {
            $groupValues[$datasetName] = $this->convert(
                $groupValues[$datasetName],
                $datasetName
            );
        }

        $data[$dataset->getCollectiveGroupName()] = $groups;

        $dataset->setData($data);

        return $dataset;
    }

}
