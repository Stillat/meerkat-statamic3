<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\DataObject;

/**
 * Class ThreadMetaData
 *
 * Represents a thread's meta data and associated data.
 *
 * @since 2.0.0
 */
class ThreadMetaData
{
    use DataObject;

    const KEY_IS_TRASHED = 'trashed';

    const KEY_CREATED_UTC = 'created';

    const KEY_ATTRIBUTES = 'attributes';

    /**
     * A collection of data-attributes related to the thread.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Indicates if the thread has been deleted.
     *
     * @var bool
     */
    protected $isTrashed = false;

    /**
     * The timestamp the thread was created on.
     *
     * @var int
     */
    protected $createdUtc = 0;

    /**
     * The timestamp the thread's context was created on.
     *
     * @var int
     */
    protected $contextCreatedUtc = 0;

    /**
     * Attempts to create a thread from the provided data.
     *
     * @param  array  $data The thread data.
     * @return ThreadMetaData
     */
    public static function makeFromArray($data)
    {
        $threadMeta = new ThreadMetaData();

        $threadMeta->fromArray($data);

        return $threadMeta;
    }

    /**
     * Sets the internal properties to match the supplied data.
     *
     * @param  array  $data The data to use.
     */
    public function fromArray($data)
    {
        if (array_key_exists(self::KEY_CREATED_UTC, $data)) {
            $this->setCreatedOn($data[self::KEY_CREATED_UTC]);
        }

        if (array_key_exists(self::KEY_IS_TRASHED, $data)) {
            $this->setIsTrashed($data[self::KEY_IS_TRASHED]);
        }

        if (array_key_exists(self::KEY_ATTRIBUTES, $data)) {
            $this->setDataAttributes($data[self::KEY_ATTRIBUTES]);
        }
    }

    /**
     * Sets the timestamp the thread was created on.
     *
     * @param  int  $time The timestamp.
     */
    public function setCreatedOn($time)
    {
        $this->createdUtc = $time;
    }

    /**
     * Sets the timestamp the thread's context was created on.
     *
     * @param  int  $time The timestamp.
     */
    public function setContextCreatedOn($time)
    {
        $this->contextCreatedUtc = $time;
    }

    /**
     * Updates the thread's meta data using the values from the provided dataset.
     *
     * Creation time is only updated if the incoming value is older than the current value.
     *
     * @param  ThreadMetaData  $newData The data to update the current state with.
     */
    public function update(ThreadMetaData $newData)
    {
        if ($this->createdUtc == 0 || ($newData->getCreatedUtc() !== 0 && $newData->getCreatedUtc() < $this->createdUtc)) {
            $this->createdUtc = $newData->getCreatedUtc();
        }

        $this->isTrashed = $newData->getIsTrashed();
    }

    /**
     * Gets the timestamp the thread was created on.
     *
     * @return int
     */
    public function getCreatedUtc()
    {
        return $this->createdUtc;
    }

    /**
     * Gets the timestamp the thread's context was created on.
     *
     * @return int
     */
    public function getContextCreatedOnUtc()
    {
        return $this->contextCreatedUtc;
    }

    /**
     * Gets whether or not the thread has been deleted.
     *
     * @return bool
     */
    public function getIsTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * Sets whether or not the thread has been deleted.
     *
     * @param  bool  $isTrashed Whether the thread is deleted.
     */
    public function setIsTrashed($isTrashed)
    {
        $this->isTrashed = $isTrashed;
    }

    /**
     * Converts the thread meta data instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_IS_TRASHED => $this->isTrashed,
            self::KEY_CREATED_UTC => $this->createdUtc,
            self::KEY_ATTRIBUTES => $this->attributes,
        ];
    }
}
