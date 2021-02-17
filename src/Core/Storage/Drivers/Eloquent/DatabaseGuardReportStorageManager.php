<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\GuardReportStorageManagerContract;
use Stillat\Meerkat\Core\Guard\SpamCheckReport;

class DatabaseGuardReportStorageManager implements GuardReportStorageManagerContract
{

    /**
     * Attempts to locate the guard report for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return SpamCheckReport|null
     */
    public function getReportForCommentId($commentId)
    {
        // TODO: Implement getReportForCommentId() method.
    }

    /**
     * Attempts to locate the guard report for the provided comment.
     *
     * @param CommentContract $comment
     * @return SpamCheckReport|null
     */
    public function getGuardReportForComment(CommentContract $comment)
    {
        // TODO: Implement getGuardReportForComment() method.
    }

    /**
     * Attempts to save the Guard report for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param SpamCheckReport $report The report to persist.
     * @return bool
     */
    public function addGuardReportById($commentId, SpamCheckReport $report)
    {
        // TODO: Implement addGuardReportById() method.
    }

    /**
     * Attempts to save the Guard report for the provided comment.
     *
     * @param CommentContract $comment The comment to save the report for.
     * @param SpamCheckReport $report The report to persist.
     * @return bool
     */
    public function addGuardReport(CommentContract $comment, SpamCheckReport $report)
    {
        // TODO: Implement addGuardReport() method.
    }

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeGuardReportById($commentId)
    {
        // TODO: Implement removeGuardReportById() method.
    }

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function removeGuardReport(CommentContract $comment)
    {
        // TODO: Implement removeGuardReport() method.
    }

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function hasGuardReportById($commentId)
    {
        // TODO: Implement hasGuardReportById() method.
    }

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param CommentContract $guard The comment.
     * @return bool
     */
    public function hasGuardReport(CommentContract $guard)
    {
        // TODO: Implement hasGuardReport() method.
    }
}
