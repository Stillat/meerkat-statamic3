<?php

namespace Stillat\Meerkat\Identity;

use Statamic\Auth\User;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;

/**
 * Class StatamicAuthorFactory
 *
 * Handles the creation of Meerkat Core identities from various Statamic contexts.
 *
 * @package Stillat\Meerkat\Identity
 * @since 1.0.0
 */
class StatamicAuthorFactory implements AuthorFactoryContract
{

    /**
     * Constructs a valid AuthorContract instance from the prototype.
     *
     * @param array $protoAuthor
     * @return AuthorContract
     */
    public function makeAuthor($protoAuthor)
    {
        if (is_array($protoAuthor)) {
            return $this->makeAuthorFromArrayPrototype($protoAuthor);
        }

        if ($protoAuthor instanceof User) {
            return $this->makeAuthorFromStatamicUser($protoAuthor);
        }

        return null;
    }

    private function makeAuthorFromArrayPrototype($protoAuthor)
    {
        return null;
    }

    /**
     * Creates a Meerkat Core identity from the provided Statamic User.
     *
     * @param User $protoUser The Statamic User instance.
     * @return StatamicIdentity
     */
    private function makeAuthorFromStatamicUser(User $protoUser)
    {
        $identity = new StatamicIdentity();

        $identity->setId($protoUser->getAuthIdentifier());
        $identity->setIsTransient(false);
        $identity->setDisplayName($protoUser->name());

        return $identity;
    }

}
