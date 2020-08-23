<?php

namespace Stillat\Meerkat\Core\Data\Mutations;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;

/**
 * Class ChangeSet
 *
 * Represents a set of changes between an attribute set.
 *
 * @package Stillat\Meerkat\Core\Data\Mutations
 * @since 2.0.0
 */
class ChangeSet
{

    const KEY_TIMESTAMP = 'timestamp';
    const KEY_USER_ID = 'user';
    const KEY_ORIGINAL_KEY_ORDER = 'oko';
    const KEY_NEW_SERIALIZED = 'new';
    const KEY_OLD_SERIALIZED = 'old';

    /**
     * The timestamp of the change-set.
     *
     * @var int
     */
    protected $timestampUtc = 0;

    /**
     * The new attributes.
     *
     * @var array
     */
    protected $newAttributes = [];

    /**
     * A list of removed attributes.
     *
     * @var array
     */
    protected $removedAttributes = [];

    /**
     * A list of attributes that were not modified.
     *
     * @var array
     */
    protected $unchangedAttributes = [];

    /**
     * A list of attributes that were modified.
     *
     * @var array
     */
    protected $changedAttributes = [];

    /**
     * A list of all the original keys, in their original order.
     *
     * @var array
     */
    protected $originalKeyOrdering = [];

    /**
     * A serialized representation of the old data.
     *
     * @var string
     */
    protected $oldSerialized = '';

    /**
     * A serialized representation of the new data.
     *
     * @var string
     */
    protected $newSerialized = '';

    /**
     * The current identity, if any.
     *
     * @var AuthorContract|null
     */
    protected $identity = null;

    public function __construct()
    {
        $this->timestampUtc = time();
    }

    /**
     * Gets a list of new attributes.
     *
     * @return array
     */
    public function getNewAttributes()
    {
        return $this->newAttributes;
    }

    /**
     * Sets the list of new attributes.
     *
     * @param array $attributes The new attributes.
     */
    public function setNewAttributes($attributes)
    {
        $this->newAttributes = $attributes;
    }

    /**
     * Gets a list of attributes that were removed.
     *
     * @return array
     */
    public function getRemovedAttributes()
    {
        return $this->removedAttributes;
    }

    /**
     * Sets the attributes that were removed.
     *
     * @param array $attributes The removed attributes.
     */
    public function setRemovedAttributes($attributes)
    {
        $this->removedAttributes = $attributes;
    }

    /**
     * Gets a list of attribute names that remained unchanged.
     *
     * @return array
     */
    public function getUnchangedAttributes()
    {
        return $this->unchangedAttributes;
    }

    /**
     * Sets a list attribute names that remained unchanged.
     *
     * @param array $attributes The unchanged attributes.
     */
    public function setUnchangedAttributes($attributes)
    {
        $this->unchangedAttributes = $attributes;
    }

    /**
     * Returns a list of all properties that were changed and their values.
     *
     * @return array
     */
    public function getChangedAttributes()
    {
        return $this->changedAttributes;
    }

    /**
     * Sets the properties that were changed, and their values.
     *
     * @param array $attributes The mutated attributes.
     */
    public function setChangedAttributes($attributes)
    {
        $this->changedAttributes = $attributes;
    }

    /**
     * Sets the serialized versions of the datasets that were compared.
     *
     * @param string $old The serialized version of the old data.
     * @param string $new The serialized version of the new data.
     */
    public function setSerializedRepresentations($old, $new)
    {
        $this->oldSerialized = $old;
        $this->newSerialized = $new;
    }

    /**
     * Sets the original key order.
     *
     * @param array $keys The original keys.
     */
    public function setOriginalKeyOrder($keys)
    {
        $this->originalKeyOrdering = $keys;
    }

    /**
     * Gets the original key order.
     *
     * @return array
     */
    public function getOriginalKeyOrder()
    {
        return $this->originalKeyOrdering;
    }

    /**
     * Gets the current identity.
     *
     * @return AuthorContract|null
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Sets the identity for the change set.
     *
     * @param AuthorContract $identity The identity to utilize.
     */
    public function setIdentity(AuthorContract $identity)
    {
        $this->identity = $identity;
    }

    /**
     * Converts the change-set to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $identityContext = null;

        if ($this->identity !== null && $this->identity instanceof AuthorContract) {
            if ($this->identity->getIsTransient() === false) {
                $identityContext = $this->identity->getId();
            }
        }

        return [
            self::KEY_TIMESTAMP => $this->getTimestampUtc(),
            self::KEY_ORIGINAL_KEY_ORDER => $this->getOriginalKeyOrder(),
            self::KEY_NEW_SERIALIZED => $this->getNewSerialized(),
            self::KEY_OLD_SERIALIZED => $this->getOldSerialized(),
            self::KEY_USER_ID => $identityContext
        ];
    }

    /**
     * Gets the change-set's timestamp.
     *
     * @return int
     */
    public function getTimestampUtc()
    {
        return $this->timestampUtc;
    }

    /**
     * Sets the change-set's timestamp.
     *
     * @param int $timestamp The change-set timestamp.
     */
    public function setTimestampUtc($timestamp)
    {
        $this->timestampUtc = $timestamp;
    }

    /**
     * Gets the serialized version of the new data.
     *
     * @return string
     */
    public function getNewSerialized()
    {
        return $this->newSerialized;
    }

    /**
     * Gets the serialized version of the old data.
     *
     * @return string
     */
    public function getOldSerialized()
    {
        return $this->oldSerialized;
    }

    /**
     * Tests if the attribute was removed in the new dataset.
     *
     * @param string $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeRemoved($attributeName)
    {
        return array_key_exists($attributeName, $this->removedAttributes);
    }

    /**
     * Tests if the attribute was introduced in the new dataset.
     *
     * @param string $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeAdded($attributeName)
    {
        return array_key_exists($attributeName, $this->newAttributes);
    }

    /**
     * Tests if the attribute was changed between the datasets.
     *
     * @param string $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeChanged($attributeName)
    {
        return array_key_exists($attributeName, $this->changedAttributes);
    }

    /**
     * Tests if any changes were detected for the attribute.
     *
     * @param string $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeMutated($attributeName)
    {
        if ($this->wasAttributeAdded($attributeName) ||
            $this->wasAttributeChanged($attributeName) ||
            $this->wasAttributeRemoved($attributeName)) {
            return true;
        }

        return false;
    }

}
