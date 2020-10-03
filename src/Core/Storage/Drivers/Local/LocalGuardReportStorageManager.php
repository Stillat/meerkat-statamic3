<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\GuardReportStorageManagerContract;
use Stillat\Meerkat\Core\Guard\SpamCheckReport;
use Stillat\Meerkat\Core\Storage\Paths;

/**
 * Class LocalGuardReportStorageManager
 *
 * Manages the interactions between Meerkat Guard Reports and a local file system.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalGuardReportStorageManager implements GuardReportStorageManagerContract
{
    const PATH_REPORT = '.guard';

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
     * Attempts to locate the guard report for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return SpamCheckReport|null
     */
    public function getReportForCommentId($commentId)
    {
        return $this->getGuardReportForComment(Comment::find($commentId));
    }

    /**
     * Attempts to locate the guard report for the provided comment.
     *
     * @param CommentContract $comment
     * @return SpamCheckReport|null
     */
    public function getGuardReportForComment(CommentContract $comment)
    {
        if ($this->hasGuardReport($comment) === false) {
            return null;
        }

        $storagePath = $this->getReportStoragePath($comment);

        $contents = $this->yamlParser->parseDocument(file_get_contents($storagePath));

        if ($contents === null || is_array($contents) === false) {
            return null;
        }

        return SpamCheckReport::fromArray($contents);
    }

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function hasGuardReport(CommentContract $comment)
    {
        $reportPath = $this->getReportStoragePath($comment);

        return file_exists($reportPath);
    }

    /**
     * Retrieves the file path for the comment's revisions.
     *
     * @param CommentContract $comment The comment to locate the path for.
     * @return string
     */
    private function getReportStoragePath(CommentContract $comment)
    {
        $storageDirectory = dirname($comment->getVirtualPath());

        return $this->paths->combine([
            $storageDirectory,
            self::PATH_REPORT
        ]);
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
        return $this->addGuardReport(Comment::find($commentId), $report);
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
        $storagePath = $this->getReportStoragePath($comment);
        $dataToSave = $this->yamlParser->toYaml($report->toArray(), null);

        $saveResults = file_put_contents($storagePath, $dataToSave);

        if ($saveResults === false) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeGuardReportById($commentId)
    {
        return $this->removeGuardReport(Comment::find($commentId));
    }

    /**
     * Attempts to remove any existing Guard reports for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return bool
     */
    public function removeGuardReport(CommentContract $comment)
    {
        if ($this->hasGuardReport($comment) === false) {
            return false;
        }

        $reportPath = $this->getReportStoragePath($comment);

        $removed = @unlink($reportPath);

        if ($removed === true) {
            return true;
        }

        return false;
    }

    /**
     * Tests if a Guard report exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function hasGuardReportById($commentId)
    {
        return $this->hasGuardReport(Comment::find($commentId));
    }

}
