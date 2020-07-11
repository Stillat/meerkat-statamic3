<?php

namespace Stillat\Meerkat\Core\Permissions;

/**
 * Class AccessManager
 *
 * Provides a consistent method for managing Meerkat user permissions.
 *
 * @package Stillat\Meerkat\Core\Permissions
 * @since 2.0.0
 */
class AccessManager
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

}