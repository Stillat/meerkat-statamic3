<?php

namespace Stillat\Meerkat\Core\Data\Concerns;

/**
 * Trait ManagesGroupMetaData
 *
 * Provides implementations for common grouped dataset methods.
 *
 * @package Stillat\Meerkat\Core\Data\Concerns
 * @since 2.0.0
 */
trait ManagesGroupMetaData
{

    /**
     * The name of all dataset groups.
     *
     * @var null|string
     */
    protected $collectiveGroupName = null;

    /**
     * The name of individual groups in a dataset.
     *
     * @var null|string
     */
    protected $groupName = null;

    /**
     * The name of each group's internal dataset.
     *
     * @var null|string
     */
    protected $datasetName = null;

    /**
     * Gets the collective group name.
     *
     * @return string
     */
    public function getCollectiveGroupName()
    {
        return $this->collectiveGroupName;
    }

    /**
     * Sets the collective group name.
     *
     * @param string $collectiveName The collective group name.
     */
    public function setCollectiveGroupName($collectiveName)
    {
        $this->collectiveGroupName = $collectiveName;
    }

    /**
     * Gets the name of individual groups.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Sets the name of individual groups.
     *
     * @param string $groupName The name of a single group.
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }

    /**
     * Sets the name of each group's dataset.
     *
     * @param string $datasetName The name of each group's dataset.
     */
    public function setGroupDatasetName($datasetName)
    {
        $this->datasetName = $datasetName;
    }

    /**
     * Gets the name of each group's dataset.
     *
     * @return string
     */
    public function getGroupDatasetName()
    {
        return $this->datasetName;
    }

}
