<?php

namespace Stillat\Meerkat\Identity;

use Stillat\Meerkat\Core\Contracts\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;

class StatamicIdentityManager implements IdentityManagerContract
{
    /**
     * Returns a value indicating if the provided author
     * contains a user identity within the host system.
     *
     * @param AuthorContract $author
     *
     * @return boolean
     */
    public function containsUserIdentity($author)
    {
        // TODO: Implement containsUserIdentity() method.
    }

    /**
     * Returns a value indicating if the provided author's
     * identity has sufficient privileges to auto-publish
     * comments within a thread. Implementations should
     * check with the access roles of the host system.
     *
     * @param AuthorContract $author
     *
     * @return boolean
     */
    public function canAutoPublishComments($author)
    {
        // TODO: Implement canAutoPublishComments() method.
    }

    /**
     * Retrieves the identity ID for the provied author information.
     *
     * @param AuthorContract $author
     *
     * @return string
     */
    public function getIdentityId($author)
    {
        // TODO: Implement getIdentityId() method.
    }

    /**
     * Retrieves the current identity from the current context.
     *
     * @return AuthorContract
     */
    public function getIdentityContext()
    {
        // TODO: Implement getIdentityContext() method.
    }

    /**
     * Retrieves an Identity object for the provided identifier.
     *
     * @param $authorId string The identifier of the author.
     * @return AuthorContract
     */
    public function locateIdentity($authorId)
    {
        // TODO: Implement locateIdentity() method.
    }
}