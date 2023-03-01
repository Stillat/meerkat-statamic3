<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Serializable;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;

/**
 * Interface ThreadContextContract
 *
 * Provides a consistent representation of cross-platform posts/pages/etc
 *
 * Thread contexts represent an entity that a comment thread
 * may be attached to. Context's are identified by an ID,
 * and may have a name associated with them. Examples of
 * typical thread contexts are photos or blog posts.
 *
 * @since 2.0.0
 */
interface ThreadContextContract extends DataObjectContract, Serializable
{
    /**
     * Returns the identifier string of the context.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the timestamp the context was created.
     *
     * @return int
     */
    public function getCreatedUtcTimestamp();

    /**
     * Returns the context's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Converts the context to an array.
     *
     * @return array
     */
    public function toArray();
}
