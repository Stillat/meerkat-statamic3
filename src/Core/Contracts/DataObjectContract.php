<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 * Interface DataObjectContract
 *
 * Provides an API for accessing dynamic data in cross-system objects
 *
 * @package Stillat\Meerkat\Core\Contracts
 * @since 2.0.0
 */
interface DataObjectContract
{

    /**
     * Gets an associative array representing all actionable data held in the data container.
     *
     * @return array
     */
    public function getDataAttributes();

    /**
     * Sets the data attributes for the current data object.
     *
     * @param  array $attributes The data attributes to set.
     */
    public function setDataAttributes($attributes);

    /**
     * Returns a value indicating if the provided attribute exists.
     *
     * @param  string  $key The key to check for existence.
     * @return boolean
     */
    public function hasDataAttribute($key);

    /**
     * Set the data on the object identified by the $key with the given $value.
     *
     * @param string $key   The key of the attribute to set.
     * @param string|mixed $value The value to set.
     *
     * @return void
     */
    public function setDataAttribute($key, $value);

    /**
     * Removes a data attribute with the given name.
     *
     * @param string $attributeName The name of the attribute to remove.
     */
    public function removeDataAttribute($attributeName);

    /**
     * Reassigns the provided attribute names and removes the source.
     *
     * @param string $sourceAttribute The source attribute.
     * @param string $targetAttribute The target attribute.
     * @return mixed
     */
    public function reassignDataProperty($sourceAttribute, $targetAttribute);

    /**
     * Get the value for the provided $key, if one exists.
     *
     * @param string      $key     The key of the attribute to get.
     * @param string|null $default The default value to return if the attribute does not exist.
     *
     * @return string|null
     */
    public function getDataAttribute($key, $default = null);
}
