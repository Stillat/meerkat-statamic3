<?php

namespace Stillat\Meerkat\Identity;

use Statamic\Auth\User;
use Statamic\Auth\UserProvider;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;

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
     * Data attribute key representing Statamic "Super User" status.
     */
    const STATAMIC_USER_IS_SUPER = 'statamic_is_super';

    /**
     * Data attribute key representing Statamic user groups.
     */
    const STATAMIC_USER_GROUPS = 'statamic_groups';

    /**
     * Data attribute key representing Statamic user roles.
     */
    const STATAMIC_USER_ROLES = 'statamic_roles';

    /**
     * A cache of previously resolved user identities.
     * @var array
     */
    public static $identityCache = [];

    /**
     * The Statamic UserProvider instance.
     *
     * @var UserProvider
     */
    private $statamicUserProvider = null;

    /**
     * The permissions manager instance.
     *
     * @var PermissionsManagerContract
     */
    private $permissionsManager = null;

    public function __construct(UserProvider $userProvider, PermissionsManagerContract $permissionsManager)
    {
        $this->statamicUserProvider = $userProvider;
        $this->permissionsManager = $permissionsManager;
    }

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

    /**
     * Creates a Meerkat Core identity from the provided prototype information.
     *
     * @param array $protoAuthor The author prototype.
     * @return StatamicIdentity|
     */
    private function makeAuthorFromArrayPrototype($protoAuthor)
    {
        if (is_array($protoAuthor) == false || count($protoAuthor) === 0) {
            return null;
        }

        // If the author prototype contains the data attribute indicating that
        // it was left by an authenticated user at some point, let's locate
        // that user; if they exist, create the Author from that user.
        if (array_key_exists(AuthorContract::AUTHENTICATED_USER_ID, $protoAuthor)) {
            /** @var User $potentialStatamicUser */
            $potentialStatamicUser = $this->statamicUserProvider->retrieveById($protoAuthor[AuthorContract::AUTHENTICATED_USER_ID]);

            if ($potentialStatamicUser !== null) {
                $identity = $this->makeAuthorFromStatamicUser($potentialStatamicUser);

                foreach ($protoAuthor as $key => $value) {
                    $identity->setDataAttribute($key, $value);

                    if ($key === AuthorContract::KEY_AUTHOR_URL) {
                        $identity->setWebAddress($value);
                    }
                }

                $identity->setPermissionsSet($this->permissionsManager->getPermissions($identity));

                return $identity;
            }
        }

        $transientIdentity = new StatamicIdentity();

        $transientIdentity->setId(null);
        $transientIdentity->setIsTransient(true);

        if (array_key_exists(AuthorContract::KEY_NAME, $protoAuthor)) {
            $transientIdentity->setDisplayName($protoAuthor[AuthorContract::KEY_NAME]);
        }

        if (array_key_exists(AuthorContract::KEY_EMAIL_ADDRESS, $protoAuthor)) {
            $transientIdentity->setEmailAddress($protoAuthor[AuthorContract::KEY_EMAIL_ADDRESS]);
        }

        if (array_key_exists(AuthorContract::KEY_AUTHOR_URL, $protoAuthor)) {
            if ($protoAuthor[AuthorContract::KEY_AUTHOR_URL] !== null) {
                $transientIdentity->setWebAddress($protoAuthor[AuthorContract::KEY_AUTHOR_URL]);
            }
        }

        // Iterate all properties and set them on the identity context.
        foreach ($protoAuthor as $key => $value) {
            $transientIdentity->setDataAttribute($key, $value);
        }

        $transientIdentity->setPermissionsSet($this->permissionsManager->getPermissions($transientIdentity));

        return $transientIdentity;
    }

    /**
     * Creates a Meerkat Core identity from the provided Statamic User.
     *
     * @param User $protoUser The Statamic User instance.
     * @return StatamicIdentity
     */
    private function makeAuthorFromStatamicUser(User $protoUser)
    {
        $protoUserIdentifier = $protoUser->getAuthIdentifier();

        if (self::$identityCache !== null && is_array(self::$identityCache)) {
            if (array_key_exists($protoUserIdentifier, self::$identityCache)) {
                return self::$identityCache[$protoUserIdentifier];
            }
        }

        $identity = new StatamicIdentity();

        $identity->setId($protoUserIdentifier);
        $identity->setIsTransient(false);
        $identity->setDisplayName($protoUser->name());
        $identity->setEmailAddress($protoUser->email());
        $identity->setDataAttribute(self::STATAMIC_USER_IS_SUPER, $protoUser->isSuper());
        $identity->setDataAttribute(self::STATAMIC_USER_GROUPS, $protoUser->groups());
        $identity->setDataAttribute(self::STATAMIC_USER_ROLES, $protoUser->roles());

        $identity->setPermissionsSet($this->permissionsManager->getPermissions($identity));

        self::$identityCache[$protoUserIdentifier] = $identity;

        return $identity;
    }

}
