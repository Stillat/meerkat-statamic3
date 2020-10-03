<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Guard\SpamCheckReport;

/**
 * Interface GuardReportStorageManagerContract
 *
 * Provides a consistent API for persisting and interacting with comment Guard reports.
 *
 * @package Stillat\Meerkat\Core\Contracts\Storage
 * @since 2.0.0
 */
interface GuardReportStorageManagerContract
{

    /**
     * Attempts to locate the guard report for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return SpamCheckReport|null
     */
    public function getReportForCommentId($commentId);

    /**
     * Attempts to locate the guard report for the provided comment.
     *
     * @param CommentContract $comment
     * @return SpamCheckReport|null
     */
    public function getGuardReportForComment(CommentContract $comment);

    /**
     * Attempts to save the Guard report for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param SpamCheckReport $report The report to persist.
     * @return bool
     */
    public function addGuardReportById($commentId, SpamCheckReport $report);

    /**
     * Attempts to save the Guard report for the provided comment.
     *
     * @param CommentContract $comment The comment to save the report for.
     * @param SpamCheckReport $report The report to persist.
     * @return bool
     */
    public function addGuardReport(CommentContract $comment, SpamCheckReport $report);

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeGuardReportById($commentId);

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function removeGuardReport(CommentContract $comment);

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function hasGuardReportById($commentId);

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param CommentContract $guard The comment.
     * @return bool
     */
    public function hasGuardReport(CommentContract $guard);

}
