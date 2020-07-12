<?php

namespace Stillat\Meerkat\Identity;

use Statamic\Auth\UserProvider;
use Statamic\Contracts\Auth\User;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Statamic\Contracts\Auth\UserRepository;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;

/**
 * Class StatamicIdentityManager
 *
 * Manages the resolution of identities between Meerkat Core and Statamic.
 *
 * @package Stillat\Meerkat\Identity
 * @since 1.0.0
 */
class StatamicIdentityManager implements IdentityManagerContract, PermissionsManagerContract
{

    /**
     * The Statamic UserProvider instance.
     *
     * @var Statamic\Auth\UserProvider
     */
    private $statamicUserProvider = null;

    /**
     * @var Statamic\Contracts\Auth\UserRepository
     */
    private $statamicUserRepository = null;

    /**
     * The AuthorFactory implementation instance.
     *
     * @var Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract
     */
    private $authorFactory = null;

    /**
     * The Meerkat Core configuration container instance.
     *
     * @var Stillat\Meerkat\Core\Configuration
     */
    private $config = null;

    public function __construct(UserRepository $userRepository, UserProvider $userProvider, AuthorFactoryContract $authorFactory, Configuration $coreConfig)
    {
        $this->statamicUserRepository = $userRepository;
        $this->statamicUserProvider = $userProvider;
        $this->authorFactory = $authorFactory;
        $this->config = $coreConfig;
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
        if ($author === null) {
            return false;
        }

        if ($author instanceof AuthorContract === false) {
            return false;
        }

        $statamicUser = $this->statamicUserProvider->retrieveById($author->getId());

        if ($statamicUser === null) {
            return false;
        }

        return true;
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
        if ($author === null) {
            return false;
        }

        if ($author->getIsTransient() && $this->config->autoPublishAnonymousPosts) {
            return true;
        }

        if ($author->getIsTransient() === false && $this->config->autoPublishAuthenticatedPosts) {
            $currentContext = $this->getIdentityContext();

            if ($currentContext === null) {
                return false;
            }

            if ($author->getId() === $currentContext->getId()) {
                return true;
            }
        }

        return false;
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
        if (is_array($author)) {
            return $this->getIdentityId($this->authorFactory->makeAuthor($author));
        }

        if ($author instanceof AuthorContract) {
            if ($author->getIsTransient()) {
                return null;
            }

            return $author->getId();
        }

        if ($author instanceof User) {
            $identity = $this->authorFactory->makeAuthor($author);

            if ($identity !== null) {
                return $identity->getId();
            }
        }

        return null;
    }

    /**
     * Retrieves the current identity from the current context.
     *
     * @return AuthorContract
     */
    public function getIdentityContext()
    {
        $currentUser = $this->statamicUserRepository->current();

        if ($currentUser === null) {
            return null;
        }

        return $this->authorFactory->makeAuthor($currentUser);
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

    /**
     * Resolves the permissions set for the provided identity.
     *
     * @param AuthorContract $identity
     * @return PermissionsSet
     */
    public function getPermissions(AuthorContract $identity)
    {
        if ($identity === null || $identity->getId() === null) {
            $permissionsSet = new PermissionsSet();

            $permissionsSet->revokeAll();

            return $permissionsSet;
        }

        return $this->locateIdentity($identity->getId())->getPermissionSet();
    }
}
