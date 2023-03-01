<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Mail\MailReport;

/**
 * Interface EmailReportStorageManagerContract
 *
 * Provides a consistent API for managing comment submission email reports.
 *
 * @since 2.1.5
 */
interface EmailReportStorageManagerContract
{
    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param  string  $commentId The comment identifier.
     * @return bool
     */
    public function wasSentById($commentId);

    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param  CommentContract  $comment The comment.
     * @return bool
     */
    public function wasSent(CommentContract $comment);

    /**
     * Saves a mail report for the provided comment.
     *
     * @param  string  $commentId The comment identifier.
     * @param  MailReport  $report The report.
     * @return bool
     */
    public function saveReportForCommentById($commentId, MailReport $report);

    /**
     * Saves a mail report for the provided comment.
     *
     * @param  CommentContract  $comment The comment.
     * @param  MailReport  $report The report.
     * @return bool
     */
    public function saveReportForComment(CommentContract $comment, MailReport $report);

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param  string  $commentId The comment identifier.
     * @return MailReport|null
     */
    public function getReportForCommentById($commentId);

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param  CommentContract  $comment The comment.
     * @return MailReport|null
     */
    public function getReportForComment(CommentContract $comment);
}
