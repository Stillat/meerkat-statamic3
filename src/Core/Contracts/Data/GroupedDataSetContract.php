<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface GroupedDataSetContract
 *
 * Provides a standard API for interacting with grouped datasets.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data
 * @since 2.0.0
 */
interface GroupedDataSetContract extends DataSetContract
{

    /**
     * Gets the names of the data groups.
     *
     * @return string[]
     */
    public function getGroupNames();

    /**
     * Sets the names of the data groups.
     * @param array $names The names.
     * @return mixed
     */
    public function setGroupNames($names);

    /**
     * Sets the collective group name.
     *
     * @param string $collectiveName The collective group name.
     */
    public function setCollectiveGroupName($collectiveName);

    /**
     * Gets the collective group name.
     *
     * @return string
     */
    public function getCollectiveGroupName();

    /**
     * Sets the name of individual groups.
     *
     * @param string $groupName The name of a single group.
     */
    public function setGroupName($groupName);

    /**
     * Gets the name of individual groups.
     *
     * @return string
     */
    public function getGroupName();

    /**
     * Sets the name of each group's dataset.
     *
     * @param string $datasetName The name of each group's dataset.
     */
    public function setGroupDatasetName($datasetName);

    /**
     * Gets the name of each group's dataset.
     *
     * @return string
     */
    public function getGroupDatasetName();

}
