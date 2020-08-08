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
        $this->setAuthState(true);
    }

    /**
     * Sets the user's access to all applicable permissions.
     *
     * @param bool $canAccess Whether the identity has the permissions.
     */
    private function setAuthState($canAccess)
    {
        $this->canViewComments = $canAccess;
        $this->canApproveComments = $canAccess;
        $this->canUnApproveComments = $canAccess;
        $this->canReplyToComments = $canAccess;
        $this->canReplyToComments = $canAccess;
        $this->canEditComments = $canAccess;
        $this->canReportAsHam = $canAccess;
        $this->canReportAsSpam = $canAccess;
        $this->canRemoveComments = $canAccess;
    }

    /**
     * Removes all permissions from the current set.
     */
    public function revokeAll()
    {
        $this->setAuthState(false);
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
