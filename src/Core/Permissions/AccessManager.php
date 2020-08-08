<?php

namespace Stillat\Meerkat\Core\Permissions;

use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;

/**
 * Class AccessManager
 *
 * Provides a consistent method for managing Meerkat user permissions.
 *
 * @package Stillat\Meerkat\Core\Permissions
 * @since 2.0.0
 */
abstract class AccessManager implements PermissionsManagerContract
{
    const PERMISSION_ALL = 'all_permissions';
    const PERMISSION_CAN_VIEW = 'can_view_comments';
    const PERMISSION_CAN_APPROVE = 'can_approve_comments';
    const PERMISSION_CAN_UNAPPROVE = 'can_unapprove_comments';
    const PERMISSION_CAN_REPLY = 'can_reply_to_comments';
    const PERMISSION_CAN_EDIT = 'can_edit_comments';
    const PERMISSION_CAN_REPORT_SPAM = 'can_report_as_spam';
    const PERMISSION_CAN_REPORT_HAM = 'can_report_as_ham';
    const PERMISSION_CAN_REMOVE = 'can_remove_comments';


    /**
     * Returns the most restricted permission set.
     *
     * @return PermissionsSet
     */
    public function getRestrictivePermissions()
    {
        $permissionSet = new PermissionsSet();

        $permissionSet->canViewComments = false;
        $permissionSet->canApproveComments = false;
        $permissionSet->canUnApproveComments = false;
        $permissionSet->canReplyToComments = false;
        $permissionSet->canEditComments = false;
        $permissionSet->canReportAsHam = false;
        $permissionSet->canReportAsSpam = false;
        $permissionSet->canRemoveComments = false;

        return $permissionSet;
    }

}
