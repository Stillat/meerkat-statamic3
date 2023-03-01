<?php

namespace Stillat\Meerkat\Core\Data\Mutations;

/**
 * Class AttributeDiff
 *
 * Provides utilities for generating a ChangeSet from an attribute set.
 *
 * @since 2.0.0
 */
class AttributeDiff
{
    /**
     * Generates a change-set from the current and new properties.
     *
     * @param  array  $currentProperties The current properties.
     * @param  array  $newProperties The new properties.
     * @return ChangeSet
     */
    public static function analyze($currentProperties, $newProperties)
    {
        $currentSortOrder = array_keys($currentProperties);

        ksort($currentProperties);
        ksort($newProperties);

        $currentRepresentation = json_encode($currentProperties);
        $newRepresentation = json_encode($newProperties);

        $additions = array_diff($newProperties, $currentProperties);
        $removals = array_diff($currentProperties, $newProperties);
        $noChanges = [];
        $changedProperties = [];

        foreach ($currentProperties as $attributeName => $attributeValue) {
            if (array_key_exists($attributeName, $newProperties)) {
                if ($attributeValue === $newProperties[$attributeName]) {
                    $noChanges[] = $attributeName;
                } else {
                    $changedProperties[$attributeName] = [
                        $attributeValue,
                        $newProperties[$attributeName],
                    ];
                }
            }
        }

        $changeset = new ChangeSet();
        $changeset->setNewAttributes($additions);
        $changeset->setRemovedAttributes($removals);
        $changeset->setUnchangedAttributes($noChanges);
        $changeset->setChangedAttributes($changedProperties);
        $changeset->setSerializedRepresentations($currentRepresentation, $newRepresentation);
        $changeset->setOriginalKeyOrder($currentSortOrder);

        return $changeset;
    }
}
