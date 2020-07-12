<?php

namespace Stillat\Meerkat\Core;

/**
 * Trait DataObject
 *
 * The DataObject is the base logical storage unit used by Meerkat. The
 * DataObject should be considered isolated from the host system. A
 * host system may augment, provide, or supplement the storage
 * mechanisms, but should not attempt to directly control
 * the underlying data representations provided here.
 *
 * Attributes
 * ----------
 * Attributes are a key/value collection of properties associated with
 * the entity being represented by the DataObject. Typical attributes
 * include a thread or comment's ID, the author of a comment, etc.
 *
 * The storage mechanism of attributes is YAML, and any data structure
 * that can be represented in the YAML format is a valid attribute.
 *
 * Meerkat's storage mechanisms will sort attributes alphabetically
 * when persisting them to disk. Do not assume a consistent order.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
trait DataObject
{

    /**
     * Returns access to the unique identifier generator.
     *
     * @return \Stillat\Meerkat\Core\Contracts\UniqueIdentifierGeneratorContract
     */
    protected function getIdGenerator()
    {
        return $this->uidGenerator;
    }

    /**
     * Generates and returns a UUIDv4 string identifier.
     *
     * @return string
     */
    protected function getNewId()
    {
        $identifier = $this->getIdGenerator()->newId();

        return $identifier->toString();
    }

    /**
     * Gets an associative array representing all actionable data held in the data container.
     *
     * @return array
     */
    public function getDataAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns a value indicating if the provided attribute exists.
     *
     * @param  string  $key The key to check for existence.
     * @return boolean
     */
    public function hasDataAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Sets the data attributes for the current data object.
     *
     * @param  array $attributes The data attributes to set.
     */
    public function setDataAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Merges the attributes into the current data object.
     *
     * @param array $attributes The attributes to merge.
     */
    public function mergeAttributes($attributes)
    {
        if ($attributes === null || is_array($attributes) === false || count($attributes) === 0) {
            return;
        }

        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Set the data on the object identified by the $key with the given $value.
     *
     * @param string $key   The key of the attribute to set.
     * @param string $value The value to set.
     *
     * @return void
     */
    public function setDataAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Get the value for the provided $key, if one exists.
     *
     *
     * @param string      $key     The key of the attribute to get.
     * @param string|null $default The default value to return if the attribute does not exist.
     *
     * @return string|null
     */
    public function getDataAttribute($key, $default = null)
    {
        if ($this->hasDataAttribute($key)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * Returns a string representation of the current data object.
     *
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->getDataAttributes());
    }

    /**
     * Returns a run-time instance of an object from serialized form.
     *
     * @param  string $serialized
     * @return static
     */
    public function unserialize($serialized)
    {
        $this->setDataAttributes(json_decode($serialized));
    }
}
