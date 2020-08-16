<?php

namespace Stillat\Meerkat\Core\Contracts\Identity;

use Serializable;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;

/**
 * Interface AuthorContract
 *
 * Provides a standardized structure for defining authors
 *
 * Author Transience
 * -----------------
 * An author is transient if the host system does not contain
 * a record for the user, or identity, that represents the
 * author of a post. Transient authors are one-off, and
 * are typically "anonymous". Data associations can
 * be made to infer a transient author authored
 * many different posts across many threads.
 *
 * Author Identity
 * ---------------
 * In Core, the presence of an identity implies some level
 * of authentication or access-rights to the host system.
 * Core does not provide identities out-of-box;
 * implementations of Core must supply them.
 *
 * An example of an author identity association would be
 * a site administrator replying to a visitor comment.
 * The administrator's identity would be associated
 * with the postings stored author information.
 *
 * @package Stillat\Meerkat\Core\Contracts\Identity
 * @since 2.0.0
 */
interface AuthorContract extends DataObjectContract, Serializable
{

    const KEY_USER_IP = 'user_ip';
    const KEY_USER_AGENT = 'user_agent';
    const KEY_EMAIL_ADDRESS = 'email';
    const KEY_NAME = 'name';
    const AUTHENTICATED_USER_ID = 'authenticated_user';
    const KEY_USER = 'user';
    const KEY_HAS_USER = 'has_user';
    const KEY_PERMISSIONS = 'permissions';

    /**
     * Returns a value indicating if the author context is
     * persistent within the host system, or isolated
     * to the entity it is attached to. A transient
     * author is not represented in the host sys.
     *
     * @return boolean
     */
    public function getIsTransient();

    /**
     * Sets whether or not the user is persisted in the host system.
     *
     * @param boolean $isTransient Whether or not the user is persisted.
     * @return void
     */
    public function setIsTransient($isTransient);

    /**
     * Returns the string identifier for the author.
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the user string identifier.
     *
     * @param string $userId
     * @return void
     */
    public function setId($userId);

    /**
     * Attempts to locate and return the display name for the author.
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * Sets the display name for the current author context.
     *
     * @param string $displayName The display name to use for this author.
     * @return void
     */
    public function setDisplayName($displayName);

    /**
     * Gets the identity's email address, if available.
     *
     * @return mixed
     */
    public function getEmailAddress();

    /**
     * Sets the identity's email address.
     *
     * @param string $emailAddress The identity's email address.
     * @return mixed
     */
    public function setEmailAddress($emailAddress);

    /**
     * Gets the author context's permission set.
     *
     * @return PermissionsSet
     */
    public function getPermissionSet();

    /**
     * Sets the author context's permission set.
     *
     * @param PermissionsSet $permissionSet
     * @return mixed
     */
    public function setPermissionsSet($permissionSet);

    /**
     * Gets the host system's user object, if available.
     *
     * @return mixed
     */
    public function getHostUser();

    /**
     * Converts the author data into an array.
     *
     * @return array
     */
    public function toArray();

}
