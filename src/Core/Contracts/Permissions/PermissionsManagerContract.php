<?php

namespace Stillat\Meerkat\Core\Contracts\Permissions;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;

interface PermissionsManagerContract
{

    /**
     * Resolves the permissions set for the provided identity.
     *
     * @param AuthorContract $identity
     * @return PermissionsSet
     */
    public function getPermissions(AuthorContract $identity);

}
