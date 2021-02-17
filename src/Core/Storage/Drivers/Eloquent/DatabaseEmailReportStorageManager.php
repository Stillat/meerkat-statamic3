<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\EmailReportStorageManagerContract;
use Stillat\Meerkat\Core\Mail\MailReport;

class DatabaseEmailReportStorageManager implements EmailReportStorageManagerContract
{

    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function wasSentById($commentId)
    {
        // TODO: Implement wasSentById() method.
    }

    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function wasSent(CommentContract $comment)
    {
        // TODO: Implement wasSent() method.
    }

    /**
     * Saves a mail report for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param MailReport $report The report.
     * @return bool
     */
    public function saveReportForCommentById($commentId, MailReport $report)
    {
        // TODO: Implement saveReportForCommentById() method.
    }

    /**
     * Saves a mail report for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @param MailReport $report The report.
     * @return bool
     */
    public function saveReportForComment(CommentContract $comment, MailReport $report)
    {
        // TODO: Implement saveReportForComment() method.
    }

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return MailReport|null
     */
    public function getReportForCommentById($commentId)
    {
        // TODO: Implement getReportForCommentById() method.
    }

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return MailReport|null
     */
    public function getReportForComment(CommentContract $comment)
    {
        // TODO: Implement getReportForComment() method.
    }

}
