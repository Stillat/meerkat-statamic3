<?php

namespace Stillat\Meerkat\Core\Data\Mutations;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Identity\IdentityManagerFactory;

/**
 * Class ChangeSet
 *
 * Represents a set of changes between an attribute set.
 *
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
     * Converts the array into a ChangeSet instance.
     *
     * @param  array  $array The array to convert.
     * @return ChangeSet
     */
    public static function fromArray($array)
    {
        $changeSetToReturn = new ChangeSet();

        if (array_key_exists(self::KEY_TIMESTAMP, $array)) {
            $changeSetToReturn->setTimestampUtc($array[self::KEY_TIMESTAMP]);
        }

        if (array_key_exists(self::KEY_ORIGINAL_KEY_ORDER, $array)) {
            $changeSetToReturn->setOriginalKeyOrder(json_decode($array[self::KEY_ORIGINAL_KEY_ORDER]));
        }

        if (array_key_exists(self::KEY_OLD_SERIALIZED, $array) && array_key_exists(self::KEY_NEW_SERIALIZED, $array)) {
            $changeSetToReturn->setSerializedRepresentations(
                $array[self::KEY_OLD_SERIALIZED],
                $array[self::KEY_NEW_SERIALIZED]
            );
        }

        if (array_key_exists(self::KEY_USER_ID, $array)) {
            if ($array[self::KEY_USER_ID] !== null) {
                if (IdentityManagerFactory::hasInstance()) {
                    $identity = IdentityManagerFactory::$instance->locateIdentity($array[self::KEY_USER_ID]);

                    if ($identity !== null) {
                        $changeSetToReturn->setIdentity($identity);
                    }
                }
            }
        }

        $changeSetToReturn->hydrateFromSerialized();

        return $changeSetToReturn;
    }

    /**
     * Sets the original key order.
     *
     * @param  array  $keys The original keys.
     */
    public function setOriginalKeyOrder($keys)
    {
        $this->originalKeyOrdering = $keys;
    }

    /**
     * Sets the serialized versions of the datasets that were compared.
     *
     * @param  string  $old The serialized version of the old data.
     * @param  string  $new The serialized version of the new data.
     */
    public function setSerializedRepresentations($old, $new)
    {
        $this->oldSerialized = $old;
        $this->newSerialized = $new;
    }

    /**
     * Re-hydrates the mutated properties from the serialized contents.
     */
    public function hydrateFromSerialized()
    {
        $oldContent = $this->getOldProperties();
        $newContent = $this->getNewProperties();

        $changeSet = AttributeDiff::analyze($oldContent, $newContent);

        $this->setChangedAttributes($changeSet->getChangedAttributes());
        $this->setUnchangedAttributes($changeSet->getUnchangedAttributes());
        $this->setRemovedAttributes($changeSet->getRemovedAttributes());
        $this->setNewAttributes($changeSet->getNewAttributes());
    }

    /**
     * Deserializes the old encoded properties and returns an array.
     *
     * @return array
     */
    public function getOldProperties()
    {
        return $this->getProperties($this->oldSerialized);
    }

    /**
     * Deserializes the content and returns an array.
     *
     * @param  string  $content The content to decode.
     * @return array
     */
    private function getProperties($content)
    {
        $content = json_decode($content);

        if (is_array($content) === false) {
            $content = (array) $content;
        }

        return $content;
    }

    /**
     * Deserializes the new encoded properties and returns an array.
     *
     * @return array
     */
    public function getNewProperties()
    {
        return $this->getProperties($this->newSerialized);
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
     * @param  array  $attributes The mutated attributes.
     */
    public function setChangedAttributes($attributes)
    {
        $this->changedAttributes = $attributes;
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
     * @param  array  $attributes The unchanged attributes.
     */
    public function setUnchangedAttributes($attributes)
    {
        $this->unchangedAttributes = $attributes;
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
     * @param  array  $attributes The removed attributes.
     */
    public function setRemovedAttributes($attributes)
    {
        $this->removedAttributes = $attributes;
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
     * @param  array  $attributes The new attributes.
     */
    public function setNewAttributes($attributes)
    {
        $this->newAttributes = $attributes;
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
     * @param  AuthorContract  $identity The identity to utilize.
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
            self::KEY_ORIGINAL_KEY_ORDER => json_encode($this->getOriginalKeyOrder()),
            self::KEY_NEW_SERIALIZED => $this->getNewSerialized(),
            self::KEY_OLD_SERIALIZED => $this->getOldSerialized(),
            self::KEY_USER_ID => $identityContext,
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
     * @param  int  $timestamp The change-set timestamp.
     */
    public function setTimestampUtc($timestamp)
    {
        $this->timestampUtc = $timestamp;
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
     * Tests if any changes were detected for the attribute.
     *
     * @param  string  $attributeName The attribute name.
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

    /**
     * Tests if the attribute was introduced in the new dataset.
     *
     * @param  string  $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeAdded($attributeName)
    {
        return array_key_exists($attributeName, $this->newAttributes);
    }

    /**
     * Tests if the attribute was changed between the datasets.
     *
     * @param  string  $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeChanged($attributeName)
    {
        return array_key_exists($attributeName, $this->changedAttributes);
    }

    /**
     * Tests if the attribute was removed in the new dataset.
     *
     * @param  string  $attributeName The attribute name.
     * @return bool
     */
    public function wasAttributeRemoved($attributeName)
    {
        return array_key_exists($attributeName, $this->removedAttributes);
    }
}
