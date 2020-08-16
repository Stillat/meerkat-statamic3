<?php

namespace Stillat\Meerkat\Core\Data\Helpers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\DataGroupBuilder;

/**
 * Class GroupFlattener
 *
 * Provides utilities to flatten a grouped dataset.
 *
 * @package Stillat\Meerkat\Core\Data\Helpers
 * @since 2.0.0
 */
class GroupFlattener
{

    /**
     * Flattens the grouped data using the provided details.
     *
     * @param array $data The grouped data.
     * @param string $collectiveGroupName The name of all groups.
     * @param string $individualGroupName The name of each individual group.
     * @param string $datasetName The name of each group's dataset.
     * @return array
     */
    public static function flatten($data, $collectiveGroupName, $individualGroupName, $datasetName)
    {
        if (array_key_exists($collectiveGroupName, $data) === false) {
            return [];
        }

        $groupedDataSets = $data[$collectiveGroupName];
        $flattenedData = [];

        foreach ($groupedDataSets as $groupName => $value) {
            /** @var CommentContract[] $groupValues */
            $groupValues = [];

            if (array_key_exists($datasetName, $value)) {
                $groupValues = $value[$datasetName];
            }

            $groupTotalItems = count($groupValues);

            $index = 0;

            foreach ($groupValues as $comment) {
                $groupData = [
                    DataGroupBuilder::KEY_TOTAL_COUNT => $groupTotalItems,
                    DataGroupBuilder::KEY_ITEM_CURRENT_INDEX => $index
                ];

                if ($comment instanceof CommentContract) {
                    $comment->setDataAttribute($individualGroupName, $groupName);
                    $comment->setDataAttribute(DataGroupBuilder::KEY_GROUP, $groupData);
                } elseif (is_array($comment)) {
                    $comment[$individualGroupName] = $groupName;
                    $comment[DataGroupBuilder::KEY_GROUP] = $groupData;
                }

                $flattenedData[] =& $comment;
                $index += 1;
            }
        }

        return $flattenedData;
    }

}
