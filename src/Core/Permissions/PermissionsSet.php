<?php

namespace Stillat\Meerkat\Core\Permissions;

/**
 * Class PermissionsSet
 *
 * Represents a collection of permissions a user may have.
 *
 * @package Stillat\Meerkat\Core\Permissions
 * @since 2.0.0
 */
class PermissionsSet
{

    /**
     * Indicates if the user may view comments.
     *
     * @var bool
     */
    public $canViewComments = true;

    /**
     * Indicates if the user may approve comments.
     *
     * @var bool
     */
    public $canApproveComments = true;

    /**
     * Indicates if the user can un-approve comments.
     *
     * @var bool
     */
    public $canUnApproveComments = true;

    /**
     * Indicates if the user can reply to comments.
     *
     * @var bool
     */
    public $canReplyToComments = true;

    /**
     * Indicates if the user can edit comments.
     *
     * @var bool
     */
    public $canEditComments = true;

    /**
     * Indicates if the user can report comments as spam.
     *
     * @var bool
     */
    public $canReportAsSpam = true;

    /**
     * Indicates if the user can report comments as not-spam.
     *
     * @var bool
     */
    public $canReportAsHam = true;

    /**
     * Indicates if the user can delete comments.
     *
     * @var bool
     */
    public $canRemoveComments = true;

    /**
     * Grants all permissions to the current set.
     */
    public function grantAll()
    {
        $this->canViewComments = true;
        $this->canApproveComments = true;
        $this->canUnApproveComments = true;
        $this->canReplyToComments = true;
        $this->canReplyToComments = true;
        $this->canEditComments = true;
        $this->canReportAsHam = true;
        $this->canReportAsSpam = true;
        $this->canRemoveComments = true;
    }

    /**
     * Removes all permissions from the current set.
     */
    public function revokeAll()
    {
        $this->canViewComments = false;
        $this->canApproveComments = false;
        $this->canUnApproveComments = false;
        $this->canReplyToComments = false;
        $this->canReplyToComments = false;
        $this->canEditComments = false;
        $this->canReportAsHam = false;
        $this->canReportAsSpam = false;
        $this->canRemoveComments = false;
    }

    /**
     * Converts the permission set to a list an array of permission attributes.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            AccessManager::PERMISSION_CAN_VIEW => $this->canViewComments,
            AccessManager::PERMISSION_CAN_APPROVE => $this->canApproveComments,
            AccessManager::PERMISSION_CAN_UNAPPROVE => $this->canUnApproveComments,
            AccessManager::PERMISSION_CAN_REPLY => $this->canReplyToComments,
            AccessManager::PERMISSION_CAN_EDIT => $this->canEditComments,
            AccessManager::PERMISSION_CAN_REPORT_SPAM => $this->canReportAsSpam,
            AccessManager::PERMISSION_CAN_REPORT_HAM => $this->canReportAsHam,
            AccessManager::PERMISSION_CAN_REMOVE => $this->canRemoveComments
        ];
    }

}
