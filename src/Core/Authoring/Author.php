<?php

namespace Stillat\Meerkat\Core\Authoring;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\DataObject;

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
 * @since 2.0.0
 */
abstract class Author implements AuthorContract
{
    use DataObject;

    /**
     * Indicates whether or not the user is persisted in the host system.
     *
     * @var bool
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
     * The author's web address, if available.
     *
     * @var string
     */
    protected $authorWebAddress = '';

    /**
     * Returns a value indicating if the author context is
     * persistent within the host system, or isolated
     * to the entity it is attached to. A transient
     * author is not represented in the host sys.
     *
     * @return bool
     */
    public function getIsTransient()
    {
        return $this->authorIsTransient;
    }

    /**
     * Sets whether or not the user is persisted in the host system.
     *
     * @param  bool  $isTransient Whether or not the user is persisted.
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
     * @param  string  $userId
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
     * @param  string  $displayName The display name to use for this author.
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->authorDisplayName = $displayName;
    }

    /**
     * Gets the identity's web address, if available.
     *
     * @return mixed
     */
    public function getWebAddress()
    {
        return $this->authorWebAddress;
    }

    /**
     * Sets the identity's web address.
     *
     * @param  string  $webAddress The web address.
     */
    public function setWebAddress($webAddress)
    {
        $this->authorWebAddress = $webAddress;
    }
}
