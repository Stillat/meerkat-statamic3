<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Core\Permissions\AccessManager;

trait ConfiguresPermissions
{
    use UsesConfig;


    /**
     * Sets the role-based permission rules from the current runtime configuration.
     */
    protected function setPermissionsFromConfig()
    {
        $this->setPermissions($this->getConfig('permissions', []));
    }

    /**
     * Sets the role-based permissions rules.
     *
     * @param array $permissions The user-configured permission rules.
     */
    protected function setPermissions($permissions)
    {
        if ($this->permissionsConfigured === true) {
            return;
        }

        $this->configuredPermissions = $permissions;

        $allPermissionKeys = [AccessManager::PERMISSION_ALL, AccessManager::PERMISSION_CAN_VIEW, AccessManager::PERMISSION_CAN_APPROVE,
            AccessManager::PERMISSION_CAN_UNAPPROVE, AccessManager::PERMISSION_CAN_REPLY, AccessManager::PERMISSION_CAN_EDIT,
            AccessManager::PERMISSION_CAN_REPORT_HAM, AccessManager::PERMISSION_CAN_REPORT_SPAM, AccessManager::PERMISSION_CAN_REMOVE];

        foreach ($allPermissionKeys as $permissionCategory) {
            if (array_key_exists($permissionCategory, $this->configuredPermissions) == false) {
                $this->configuredPermissions[$permissionCategory] = [];
            } else if ($this->configuredPermissions[$permissionCategory] === null) {
                $this->configuredPermissions[$permissionCategory] = [];
            }
        }

        // Get rid of the PERMISSION_ALL entry.
        array_shift($allPermissionKeys);

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_ALL])) {
            $this->configuredPermissions[AccessManager::PERMISSION_ALL] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_VIEW])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_VIEW] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_APPROVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_APPROVE] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_UNAPPROVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_UNAPPROVE] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPLY])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPLY] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_EDIT])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_EDIT] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_SPAM])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_SPAM] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_HAM])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REPORT_HAM] = [];
        }

        if (!is_array($this->configuredPermissions[AccessManager::PERMISSION_CAN_REMOVE])) {
            $this->configuredPermissions[AccessManager::PERMISSION_CAN_REMOVE] = [];
        }

        if (count($this->configuredPermissions[AccessManager::PERMISSION_ALL]) > 0) {
            foreach ($this->configuredPermissions[AccessManager::PERMISSION_ALL] as $userRole) {
                foreach ($allPermissionKeys as $permissionCategory) {
                    if (in_array($userRole, $this->configuredPermissions[$permissionCategory]) == false) {
                        array_push($this->configuredPermissions[$permissionCategory], $userRole);
                    }
                }
            }
        }

        // Calculate a role count.
        foreach ($allPermissionKeys as $permissionCategory) {
            $this->totalRoleCount += count($this->configuredPermissions[$permissionCategory]);
        }

        $this->permissionsConfigured = true;
    }


}