<?php

namespace Stillat\Meerkat\Core\Data\Concerns;

/**
 * Trait ContainsGroups
 *
 * Provides functionality to manage data-set groups.
 *
 * @package Stillat\Meerkat\Core\Data\Concerns
 * @since 2.0.0
 */
trait ContainsGroups
{

    /**
     * A collection of group names.
     *
     * @var string[]
     */
    protected $groupNames = [];

    /**
     * Gets the group names.
     *
     * @return array
     */
    public function getGroupNames()
    {
        return $this->groupNames;
    }

    /**
     * Sets the group names.
     *
     * @param string[] $names The names.
     */
    public function setGroupNames($names)
    {
        $this->groupNames = $names;
    }

}
