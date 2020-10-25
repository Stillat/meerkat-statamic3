<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\EmailReportStorageManagerContract;
use Stillat\Meerkat\Core\Mail\MailReport;
use Stillat\Meerkat\Core\Storage\Paths;

/**
 * Class LocalEmailReportStorageManager
 *
 * Manages the interactions between Meerkat Mail Reports and a local file system.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.1.5
 */
class LocalEmailReportStorageManager implements EmailReportStorageManagerContract
{
    const EXT_REPORT = '.mail_report';

    /**
     * The Meerkat Core configuration.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The Paths instance.
     *
     * @var Paths
     */
    protected $paths = null;

    /**
     * The YAMLParserContract implementation instance.
     *
     * @var YAMLParserContract
     */
    protected $yamlParser = null;

    public function __construct(Configuration $config, YAMLParserContract $yamlParser)
    {
        $this->config = $config;
        $this->paths = new Paths($this->config);
        $this->yamlParser = $yamlParser;
    }

    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function wasSentById($commentId)
    {
        $comment = Comment::find($commentId);

        if ($comment === null) {
            return false;
        }

        return $this->wasSent($comment);
    }

    /**
     * Indicates if mail was already sent for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function wasSent(CommentContract $comment)
    {
        return file_exists($this->getReportPath($comment));
    }

    /**
     * Retrieves the file path for the mail report.
     *
     * @param CommentContract $comment The comment.
     * @return string
     */
    private function getReportPath(CommentContract $comment)
    {
        $storageDirectory = dirname($comment->getVirtualPath());

        return $this->paths->combine([
            $storageDirectory,
            self::EXT_REPORT
        ]);
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
        $comment = Comment::find($commentId);

        if ($comment === null) {
            return false;
        }

        return $this->saveReportForComment($comment, $report);
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
        $storagePath = $this->getReportPath($comment);
        $dataToSave = $this->yamlParser->toYaml($report->toArray(), null);

        $saveResults = file_put_contents($storagePath, $dataToSave);

        if ($saveResults === false) {
            return false;
        }

        return true;
    }

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return MailReport|null
     */
    public function getReportForCommentById($commentId)
    {
        $comment = Comment::find($commentId);

        if ($comment === null) {
            return null;
        }

        return $this->getReportForComment($comment);
    }

    /**
     * Locates an existing mail report for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return MailReport|null
     */
    public function getReportForComment(CommentContract $comment)
    {
        $storagePath = $this->getReportPath($comment);
        $contents = $this->yamlParser->parseDocument(file_get_contents($storagePath));

        if ($contents === null || is_array($contents) === false) {
            return null;
        }

        return MailReport::fromArray($contents);
    }

}
