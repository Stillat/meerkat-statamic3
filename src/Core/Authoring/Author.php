<?php

namespace Stillat\Meerkat\Core\Authoring;

use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;

/**
 * Class Author
 *
 * Author represents some user-identity context around comments
 *
 * Authors may be anonymous, or have a "real" mapping
 * in the host system. Collections of authors may
 * also be present on a thread or comment as a
 * named collection of thread "Participants".
 *
 * @package Stillat\Meerkat\Core\Authoring
 * @since 2.0.0
 */
abstract class Author implements AuthorContract
{
    use DataObject;

    /**
     * Indicates whether or not the user is persisted in the host system.
     *
     * @var boolean
     */
    protected $authorIsTransient = true;

    /**
     * The user ID in the host system, if available.
     *
     * @var string|null
     */
    protected $authorUserId = null;

    /**
     * The display name of the author, if available.
     *
     * @var string
     */
    protected $authorDisplayName = '';

    /**
     * Returns a value indicating if the author context is
     * persistent within the host system, or isolated
     * to the entity it is attached to. A transient
     * author is not represented in the host sys.
     *
     * @return boolean
     */
    public function getIsTransient()
    {
        return $this->authorIsTransient;
    }

    /**
     * Sets whether or not the user is persisted in the host system.
     *
     * @param  boolean $isTransient Whether or not the user is persisted.
     * @return void
     */
    public function setIsTransient($isTransient)
    {
        $this->authorIsTransient = $isTransient;
    }

    /**
     * Returns the string identifier for the author.
     *
     * @return string
     */
    public function getId()
    {
        return $this->authorUserId;
    }

    /**
     * Sets the user string identifier.
     *
     * @param  string $userId
     * @return void
     */
    public function setId($userId)
    {
        $this->authorUserId = $userId;
    }

    /**
     * Attempts to locate and return the display name for the author.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->authorDisplayName;
    }

    /**
     * Sets the display name for the current author context.
     *
     * @param  string $displayName The display name to use for this author.
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->authorDisplayName = $displayName;
    }

}
