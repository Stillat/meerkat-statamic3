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
     * Converts the array into a ChangeSetCollection.
     *
     * @param array $array The data to convert to a ChangeSetCollection.
     * @return ChangeSetCollection
     */
    public static function fromArray($array)
    {
        $collectionToReturn = new ChangeSetCollection();

        if (array_key_exists(self::KEY_REVISION, $array)) {
            $collectionToReturn->setCurrentRevision($array[self::KEY_REVISION]);
        }

        if (array_key_exists(self::KEY_CHANGE_SETS, $array)) {
            foreach ($array[self::KEY_CHANGE_SETS] as $change) {
                $collectionToReturn->addChangeSet(ChangeSet::fromArray($change));
            }
        }

        return $collectionToReturn;
    }

    /**
     * Adds a new change set to the collection.
     *
     * @param ChangeSet $changeSet The change set to add.
     */
    public function addChangeSet(ChangeSet $changeSet)
    {
        $this->changeSets[] = $changeSet;
    }

    /**
     * Tests whether the change set collection contains the requested revision.
     *
     * @param string $revision The revision's identifier.
     * @return bool
     */
    public function hasRevision($revision)
    {
        return in_array($revision, $this->getChangeSetRevisions());
    }

    /**
     * Gets the change set collection's revision identifiers.
     *
     * @return array
     */
    public function getChangeSetRevisions()
    {
        $revisions = [];

        foreach ($this->getChangeSets() as $changeSet) {
            $revisions[] = $changeSet->getTimestampUtc();
        }

        return $revisions;
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
     * Sets the collection's change sets.
     *
     * @param ChangeSet[] $changeSets The change sets.
     */
    public function setChangeSets($changeSets)
    {
        $this->changeSets = $changeSets;
    }

    /**
     * Returns the change set for the provided revision identifier.
     *
     * @param string $revision The revision's identifier.
     * @return ChangeSet|null
     */
    public function getChangeSetForRevision($revision)
    {
        foreach ($this->getChangeSets() as $changeSet) {
            if ($changeSet->getTimestampUtc() == $revision) {
                return $changeSet;
            }
        }

        return null;
    }

    /**
     * Converts the collection to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_REVISION => $this->getCurrentRevision(),
            self::KEY_CHANGE_SETS => $this->getChangeSetArray()
        ];
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
     * Returns all change sets as a list of arrays.
     *
     * @return array
     */
    public function getChangeSetArray()
    {
        $changesToReturn = [];

        foreach ($this->changeSets as $changeSet) {
            $changesToReturn[] = $changeSet->toArray();
        }

        return $changesToReturn;
    }

}