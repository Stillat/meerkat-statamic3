<?php

namespace Stillat\Meerkat\Mail;

use Exception;
use Illuminate\Support\Facades\Mail;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Mail\MailerContract;
use Stillat\Meerkat\Core\Contracts\Storage\EmailReportStorageManagerContract;
use Stillat\Meerkat\Core\Data\FieldMapper;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Mail\MailReport;
use Stillat\Meerkat\Core\Mail\MailReportItem;

/**
 * Class MeerkatMailer
 *
 * Provides a Meerkat Core MailerContract implementation based on the Laravel mail systems.
 *
 * @package Stillat\Meerkat\Mail
 * @since 2.1.5
 */
class MeerkatMailer implements MailerContract
{

    /**
     * The FieldMapper instance.
     *
     * @var FieldMapper
     */
    private $fieldMapper = null;

    /**
     * The EmailReportStorageManagerContract implementation instance.
     *
     * @var EmailReportStorageManagerContract
     */
    private $mailReportManager = null;

    public function __construct(FieldMapper $fieldMapper, EmailReportStorageManagerContract $mailReportManager)
    {
        $this->fieldMapper = $fieldMapper;
        $this->mailReportManager = $mailReportManager;
    }

    /**
     * Sends a comment submission notification to the list of email addresses.
     *
     * @param string[] $addresses The email addresses to send to.
     * @param CommentContract $comment The comment to sent.
     * @return bool
     */
    public function send($addresses, CommentContract $comment)
    {
        if ($this->mailReportManager->wasSent($comment)) {
            return true;
        }

        $allWasSuccess = true;
        $report = new MailReport();
        $reportItems = [];

        $mailable = new CommentSubmitted($comment, $this->fieldMapper);

        foreach ($addresses as $address) {
            $didSend = false;

            try {
                Mail::to($address)->send($mailable);
                $didSend = true;
            } catch (Exception $e) {
                $didSend = false;
                $allWasSuccess = false;
                ExceptionLoggerFactory::log($e);
            }

            $reportItem = new MailReportItem();
            $reportItem->setAddress($address);
            $reportItem->setDidSend($didSend);

            $reportItems[] = $reportItem;
        }

        $report->setItems($reportItems);
        $report->setCommentId($comment->getId());

        $this->mailReportManager->saveReportForComment($comment, $report);

        return $allWasSuccess;
    }

}
