<?php

namespace Stillat\Meerkat\Permissions;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Permissions\AccessManager;

/**
 * Class StatamicAccessManager
 * @package Stillat\Meerkat\Core\Permissions
 * @since 1.0.0
 */
class StatamicAccessManager extends AccessManager
{
    use UsesConfig;

    /**
     * Resolves the permissions set for the provided identity.
     *
     * @param AuthorContract $identity
     * @return PermissionsSet
     */
    public function getPermissions(AuthorContract $identity)
    {
        if ($identity->getIsTransient()) {
            return $this->getRestrictivePermissions();
        }
    }

    private function resolveStatamicPermissions()
    {
    }

}