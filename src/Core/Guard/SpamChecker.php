<?php

namespace Stillat\Meerkat\Core\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\GuardReportStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class SpamChecker
 *
 * Provides a wrapper around the SpamService.
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.0.0
 */
class SpamChecker
{

    /**
     * The SpamService instance.
     *
     * @var SpamService
     */
    protected $service = null;

    /**
     * The spam checker's unique instance identifier.
     *
     * @var string
     */
    private $instanceId = '';

    /**
     * The DataQuery instance.
     *
     * @var DataQuery
     */
    private $query = null;

    /**
     * The ThreadStorageManagerContract implementation instance.
     *
     * @var ThreadStorageManagerContract
     */
    private $threadManager = null;

    /**
     * The CommentStorageManagerContract implementation instance.
     *
     * @var CommentStorageManagerContract
     */
    private $commentManager = null;

    /**
     * The GuardReportStorageManagerContract implementation instance.
     *
     * @var GuardReportStorageManagerContract
     */
    private $reportStorageManager = null;

    public function __construct(SpamService $service, DataQuery $query, UuidGenerator $idGenerator,
                                ThreadStorageManagerContract $threadManager, CommentStorageManagerContract $commentManager,
                                GuardReportStorageManagerContract $reportStorageManager)
    {
        $this->threadManager = $threadManager;
        $this->commentManager = $commentManager;
        $this->reportStorageManager = $reportStorageManager;
        $this->service = $service;
        $this->instanceId = $idGenerator->newId();
        $this->query = $query;

        $runtimeContext = new RuntimeContext();
        $this->query->withContext($runtimeContext);
    }

    /**
     * Gets the spam checker's unique instance identifier.
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Adds all existing comments to the spam checker's scope.
     *
     * @return SpamChecker
     */
    public function checkAllComments()
    {
        $this->query->clearFilters();

        return $this;
    }

    public function check()
    {
        if ($this->service === null || $this->threadManager === null) {
            return;
        }

        $comments = $this->threadManager->getAllSystemComments();

        $this->onlyCheckNeedingReview();
        $filtered = $this->query->get($comments)->getData();

        /** @var CommentContract $comment */
        foreach ($filtered as $comment) {
            $isSpam = $this->service->isSpam($comment);

            $this->commentManager->setSpamStatus($comment, $isSpam);
            $report = $this->service->getLastReport();

            if ($isSpam) {
                $this->reportStorageManager->addGuardReport($comment, $report);
            }
        }
    }

    /**
     * Limits the scope of the spam check to only those comments not already checked.
     *
     * @return SpamChecker
     */
    public function onlyCheckNeedingReview()
    {
        $this->query->clearFilters()
            ->where(CommentContract::KEY_SPAM, '==', null);

        return $this;
    }

}
