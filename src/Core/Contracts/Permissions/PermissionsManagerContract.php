<?php

namespace Stillat\Meerkat\Core\Contracts\Permissions;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;

/**
 * Interface PermissionsManagerContract
 *
 * Provides a consistent API for retrieving permissions for an identity.
 *
 * @since 2.0.0
 */
interface PermissionsManagerContract
{
    /**
     * Resolves the permissions set for the provided identity.
     *
     * @return PermissionsSet
     */
    public function getPermissions(AuthorContract $identity);
}
