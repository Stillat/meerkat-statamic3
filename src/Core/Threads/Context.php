<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class Context
 *
 * Represents a post/page/etc in the host system.
 *
 * @since 2.0.0
 */
class Context implements ThreadContextContract
{
    use DataObject;

    const KEY_NAME = 'name';

    const KEY_REMOVE_CONTENT = 'content';

    /**
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The string identifier for the context, if available.
     *
     * @var string
     */
    public $contextId = '';

    /**
     * The name of the context, if available.
     *
     * @var string
     */
    public $contextName = '';

    /**
     * The timestamp the context was created.
     *
     * @var int
     */
    public $createdUtc = 0;

    /**
     * Returns the identifier string of the context.
     *
     * @return string
     */
    public function getId()
    {
        return $this->contextId;
    }

    /**
     * Returns the timestamp the context was created.
     *
     * @return int
     */
    public function getCreatedUtcTimestamp()
    {
        return $this->createdUtc;
    }

    /**
     * Converts the context to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->getDataAttributes();
        $attributes[self::KEY_NAME] = $this->getName();

        if (array_key_exists(self::KEY_REMOVE_CONTENT, $attributes)) {
            unset($attributes[self::KEY_REMOVE_CONTENT]);
        }

        return $attributes;
    }

    /**
     * Returns the context's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->contextName;
    }
}
