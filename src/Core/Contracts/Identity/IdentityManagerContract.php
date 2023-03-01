<?php

namespace Stillat\Meerkat\Core\Contracts\Identity;

/**
 * Interface IdentityManagerContract
 *
 * Defines an authentication and permissions interop standard
 *
 * The Identity Manager provides a method for Meerkat Core
 * to communicate to the host system about user identities
 * and system privileges. Meerkat Core will only use the
 * methods contained within this interface when determining access rights.
 *
 * @since 2.0.0
 */
interface IdentityManagerContract
{
    /**
     * Returns a value indicating if the provided author
     * contains a user identity within the host system.
     *
     * @param  AuthorContract  $author
     * @return bool
     */
    public function containsUserIdentity($author);

    /**
     * Returns a value indicating if the provided author's
     * identity has sufficient privileges to auto-publish
     * comments within a thread. Implementations should
     * check with the access roles of the host system.
     *
     * @param  AuthorContract  $author
     * @return bool
     */
    public function canAutoPublishComments($author);

    /**
     * Retrieves the identity ID for the provided author information.
     *
     * @param  AuthorContract  $author
     * @return string
     */
    public function getIdentityId($author);

    /**
     * Retrieves the current identity from the current context.
     *
     * @return AuthorContract
     */
    public function getIdentityContext();

    /**
     * Retrieves an Identity object for the provided identifier.
     *
     * @param $authorId string The identifier of the author.
     * @return AuthorContract
     */
    public function locateIdentity($authorId);
}
