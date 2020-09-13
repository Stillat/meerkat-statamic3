<?php

namespace Stillat\Meerkat\Core\Data\Helpers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class GroupMapper
 *
 * Provides utilities for modifying a grouped dataset.
 *
 * @package Stillat\Meerkat\Core\Data\Helpers
 * @since 2.0.0
 */
class GroupMapper
{

    /**
     * Mutates each element of the dataset and returns the modified dataset.
     *
     * This method does not modify the data collection provided.
     *
     * @param array $data The grouped data.
     * @param string $collectiveGroupName The name of all groups.
     * @param string $individualGroupName The name of each individual group.
     * @param string $datasetName The name of each group's dataset.
     * @param callable $callback The mutation to apply to all group elements.
     * @return array
     */
    public static function mutate($data, $collectiveGroupName, $individualGroupName, $datasetName, $callback)
    {
        $groupedDataSets = $data[$collectiveGroupName];

        foreach ($groupedDataSets as $groupName => $value) {
            /** @var CommentContract[] $groupValues */
            $groupValues = [];

            if (array_key_exists($datasetName, $value)) {
                $groupValues = $value[$datasetName];
            }

            foreach ($groupValues as $groupKey => $comment) {
                $result = $callback($comment);

                $data[$collectiveGroupName][$groupName][$datasetName][$groupKey] = $result;
            }
        }

        return $data;
    }

}
