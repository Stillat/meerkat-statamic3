<?php

namespace Stillat\Meerkat\Core\Comments;

/**
 * Class DynamicCollectedProperties
 *
 * Provides utilities for managing dynamically generated property
 * names from third-party addons or mutation pipeline actions.
 *
 * @since 2.0.0
 */
class DynamicCollectedProperties
{
    /**
     * A collection of all properties dynamically created during collection hooks.
     *
     * @var array
     */
    public static $generatedAttributes = [];

    /**
     * Registers all unique dynamic property names with the global cache.
     *
     * @param  string[]  $preMutationProperties The list of properties before a dynamic mutation.
     * @param  string[]  $postMutationProperties The list of properties after a dynamic mutation.
     */
    public static function registerDynamicProperties($preMutationProperties, $postMutationProperties)
    {
        $dynamicProperties = array_diff($postMutationProperties, $preMutationProperties);

        if (count($dynamicProperties) === 0) {
            return;
        }

        foreach ($dynamicProperties as $property) {
            if (in_array($property, self::$generatedAttributes) === false) {
                self::$generatedAttributes[] = $property;
            }
        }
    }
}
