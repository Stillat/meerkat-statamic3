<?php

namespace Stillat\Meerkat\Core\Data\Mutations;

/**
 * Class ChangeSetCollection
 *
 * Represents a collection of atomic change-sets.
 *
 * @package Stillat\Meerkat\Core\Data\Mutations
 * @since 2.0.0
 */
class ChangeSetCollection
{

    const KEY_CHANGE_SETS = 'changes';
    const KEY_REVISION = 'revision';

    /**
     * The current revision.
     *
     * @var null|int
     */
    protected $currentRevision = null;

    /**
     * The current change-sets.
     *
     * @var ChangeSet[]
     */
    protected $changeSets = [];

    /**
     * Sets the collection's change sets.
     *
     * @param ChangeSet[] $changeSets The change sets.
     */
    public function setChangeSets($changeSets)
    {
        $this->changeSets = $changeSets;
    }

    /**
     * Gets the collection's change sets.
     *
     * @return ChangeSet[]
     */
    public function getChangeSets()
    {
        return $this->changeSets;
    }

    /**
     * Sets the current change set revision.
     *
     * @param int $revision The current revision.
     */
    public function setCurrentRevision($revision)
    {
        $this->currentRevision = $revision;
    }

    /**
     * Gets the current revision.
     *
     * @return int|null
     */
    public function getCurrentRevision()
    {
        return $this->currentRevision;
    }

    public function toArray()
    {
        dd('col!', $this);
    }

}