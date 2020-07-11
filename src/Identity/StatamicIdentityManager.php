<?php

namespace Stillat\Meerkat\Identity;

use Statamic\Auth\UserProvider;
use Stillat\Meerkat\Core\Contracts\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;

class StatamicIdentityManager implements IdentityManagerContract
{

    /**
     * The Statamic UserProvider instance.
     *
     * @var UserProvider
     */
    private $statamicUserProvider = null;

    /**
     * The AuthorFactory implementation instance.
     *
     * @var AuthorFactoryContract
     */
    private $authorFactory = null;

    public function __construct(UserProvider $userProvider, AuthorFactoryContract $authorFactory)
    {
        $this->statamicUserProvider = $userProvider;
        $this->authorFactory = $authorFactory;
    }

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
     * Retrieves the identity ID for the provided author information.
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
        if ($authorId === null || mb_strlen(trim($authorId)) == 0) {
            return null;
        }

        $statamicUser = $this->statamicUserProvider->retrieveById($authorId);

        if ($statamicUser === null) {
            return null;
        }

        return $this->authorFactory->makeAuthor($statamicUser);
    }

}