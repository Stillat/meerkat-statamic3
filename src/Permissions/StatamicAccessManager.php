<?php

namespace Stillat\Meerkat\Permissions;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Permissions\AccessManager;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;
use Stillat\Meerkat\Identity\StatamicAuthorFactory;

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

        $isSuperUser = $identity->getDataAttribute(StatamicAuthorFactory::STATAMIC_USER_IS_SUPER, false);

        if ($isSuperUser) {
            return $this->getSuperUserPermissions();
        }
    }

    /**
     * Creates the least-restrictive permissions for a Statamic super user.
     *
     * @return PermissionsSet
     */
    private function getSuperUserPermissions()
    {
        $permissionSet = new PermissionsSet();

        $permissionSet->canViewComments = true;
        $permissionSet->canApproveComments = true;
        $permissionSet->canUnApproveComments = true;
        $permissionSet->canReplyToComments = true;
        $permissionSet->canEditComments = true;
        $permissionSet->canReportAsHam = true;
        $permissionSet->canReportAsSpam = true;
        $permissionSet->canRemoveComments = true;

        return $permissionSet;
    }


}