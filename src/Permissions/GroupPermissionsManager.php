<?php

namespace Stillat\Meerkat\Permissions;

use Statamic\Contracts\Auth\UserGroup;
use Statamic\Contracts\Auth\UserGroupRepository;
use Stillat\Meerkat\Concerns\ConfiguresPermissions;
use Stillat\Meerkat\Core\Permissions\AccessManager;

/**
 * Class GroupPermissionsManager
 * @package Stillat\Meerkat\Permissions
 * @since 2.1.0
 */
class GroupPermissionsManager
{
    use ConfiguresPermissions;

    protected $configuredPermissions = [];
    protected $totalRoleCount = 0;
    /**
     * The UserGroupRepository implementation instance.
     *
     * @var UserGroupRepository
     */
    private $userGroups = null;
    /**
     * The Statamic user groups, if any.
     *
     * @var UserGroup[]
     */
    private $statamicGroups = [];
    /**
     * Indicates if the user groups have been loaded.
     *
     * @var bool
     */
    private $hasLoaded = false;
    private $permissionsConfigured = false;

    private $allPermissions = [
        AccessManager::PERMISSION_CAN_VIEW,
        AccessManager::PERMISSION_CAN_APPROVE,
        AccessManager::PERMISSION_CAN_UNAPPROVE,
        AccessManager::PERMISSION_CAN_REPLY,
        AccessManager::PERMISSION_CAN_EDIT,
        AccessManager::PERMISSION_CAN_REPORT_SPAM,
        AccessManager::PERMISSION_CAN_REPORT_HAM,
        AccessManager::PERMISSION_CAN_REMOVE
    ];

    public function __construct(UserGroupRepository $userGroups)
    {
        $this->userGroups = $userGroups;

    }

    public function getPermissionMapping()
    {
        $this->loadUserGroups();
        $this->setPermissionsFromConfig();

        $groupPermissions = [];

        foreach ($this->statamicGroups as $group) {
            $groupHandle = $group->id();
            $groupName = $group->title();

            if (in_array($groupHandle, $this->configuredPermissions[AccessManager::PERMISSION_ALL])) {
                $groupPermissions[] = $this->getAllPermissionsMapping($groupHandle, $groupName);
            } else {
                $groupPermissions[] = $this->getSelectivePermissionsMapping($groupHandle, $groupName);
            }

        }

        usort($groupPermissions, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $groupPermissions;
    }

    /**
     * Reloads the Statamic user groups.
     */
    private function loadUserGroups()
    {
        if ($this->hasLoaded === true) {
            return;
        }

        $this->hasLoaded = true;
        $this->statamicGroups = $this->userGroups->all()->all();
    }

    private function getAllPermissionsMapping($handle, $title)
    {
        return [
            'name' => $title,
            'id' => $handle,
            'permissions' => [
                AccessManager::PERMISSION_ALL => true,
                AccessManager::PERMISSION_CAN_VIEW => true,
                AccessManager::PERMISSION_CAN_APPROVE => true,
                AccessManager::PERMISSION_CAN_UNAPPROVE => true,
                AccessManager::PERMISSION_CAN_REPLY => true,
                AccessManager::PERMISSION_CAN_EDIT => true,
                AccessManager::PERMISSION_CAN_REPORT_SPAM => true,
                AccessManager::PERMISSION_CAN_REPORT_HAM => true,
                AccessManager::PERMISSION_CAN_REMOVE => true
            ]
        ];
    }

    private function getSelectivePermissionsMapping($handle, $title)
    {
        $mapping = [
            'name' => $title,
            'id' => $handle,
            'permissions' => [
                AccessManager::PERMISSION_ALL => false
            ]
        ];

        foreach ($this->allPermissions as $permission) {
            $mapping['permissions'][$permission] = $this->hasPermission($handle, $permission);
        }

        return $mapping;
    }

    /**
     * Determines if the specified group has the requested permission.
     *
     * @param string $groupHandle The Statamic group's identifier.
     * @param string $permission The permission name.
     * @return bool
     */
    private function hasPermission($groupHandle, $permission)
    {
        return in_array($groupHandle, $this->configuredPermissions[$permission]);
    }


}