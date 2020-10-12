<?php

namespace Stillat\Meerkat\Core\Guard;

use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\GuardReportStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\TaskCodes;
use Stillat\Meerkat\Core\Tasks\Task;
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
    const FILTER_ONLY_PENDING = 'pending';
    const FILTER_ALL = 'all';
    const ARG_FILTER = 'filter';

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

    /**
     * The TaskStorageManagerContract implementation instance.
     *
     * @var TaskStorageManagerContract
     */
    private $taskManager = null;

    /**
     * The CommentMutationPipelineContract implementation instance.
     *
     * @var CommentMutationPipelineContract
     */
    private $commentMutationPipeline = null;

    /**
     * The comment filter applied.
     *
     * @var string
     */
    private $commentFilter = '';

    /**
     * Indicates if we should check a custom CommentContract.
     *
     * @var bool
     */
    private $checkSingle = false;

    /**
     * Indicates if any spam guard failed.
     *
     * @var bool
     */
    private $hasErrors = false;

    /**
     * A list of comments to check, provided by a developer/user.
     *
     * @var CommentContract
     */
    private $explicitComments = [];

    public function __construct(SpamService $service, DataQuery $query, UuidGenerator $idGenerator,
                                ThreadStorageManagerContract $threadManager, CommentStorageManagerContract $commentManager,
                                GuardReportStorageManagerContract $reportStorageManager, TaskStorageManagerContract $taskManager,
                                CommentMutationPipelineContract $pipelineContract)
    {
        $this->threadManager = $threadManager;
        $this->commentManager = $commentManager;
        $this->reportStorageManager = $reportStorageManager;
        $this->service = $service;
        $this->instanceId = $idGenerator->newId();
        $this->taskManager = $taskManager;
        $this->commentMutationPipeline = $pipelineContract;
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
     * Creates a spam check task and returns the TaskContract.
     *
     * @return TaskContract|null
     */
    public function check()
    {
        if ($this->service === null || $this->threadManager === null) {
            return null;
        }

        // Construct a new task object.
        $spamCheckTask = Task::taskFromMethod(TaskCodes::SPAM_CHECKER_CHECK);
        $spamCheckTask->setArguments([
            self::ARG_FILTER => $this->commentFilter
        ]);

        $this->taskManager->saveTask($spamCheckTask);

        $this->commentMutationPipeline->checkingForSpam([$spamCheckTask->getInstanceId()], function ($args) {
            $spamChecker = SpamCheckerFactory::getNew();

            $spamChecker->checkFromTask($args[0]);
        });

        return $spamCheckTask;
    }

    /**
     * Retrieves arguments from a stored task and starts the spam check.
     *
     * @param string $taskId The task identifier.
     */
    public function checkFromTask($taskId)
    {
        $task = $this->taskManager->findById($taskId);

        if ($task === null) {
            return;
        }

        $taskArgs = $task->getArguments();

        if (array_key_exists(self::ARG_FILTER, $taskArgs) === false ||
            $taskArgs[self::ARG_FILTER] === self::FILTER_ONLY_PENDING) {
            $this->onlyCheckNeedingReview();
        } else {
            $this->checkAllComments();
        }

        try {
            $this->checkCommentsNow();
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
            $this->taskManager->markCanceledById($taskId);
        }

        $this->taskManager->markCompleteById($taskId);
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
        $this->commentFilter = self::FILTER_ONLY_PENDING;

        return $this;
    }

    /**
     * Adds all existing comments to the spam checker's scope.
     *
     * @return SpamChecker
     */
    public function checkAllComments()
    {
        $this->query->clearFilters();
        $this->commentFilter = self::FILTER_ALL;

        return $this;
    }

    /**
     * Checks the comments for spam immediately.
     *
     * @return bool
     * @throws FilterException
     */
    public function checkCommentsNow()
    {
        $filtered = [];

        $spam = false;

        if ($this->checkSingle === true) {
            $filtered = $this->explicitComments;
        } else {
            $comments = $this->threadManager->getAllSystemComments();

            $this->onlyCheckNeedingReview();
            $filtered = $this->query->get($comments)->getData();
        }

        /** @var CommentContract $comment */
        foreach ($filtered as $comment) {
            $isSpam = $this->service->isSpam($comment);

            if ($this->service->hasErrors()) {
                $this->hasErrors = true;
            }

            $this->commentManager->setSpamStatus($comment, $isSpam);
            $report = $this->service->getLastReport();

            if ($isSpam) {
                $spam = true;
                $this->reportStorageManager->addGuardReport($comment, $report);
            }
        }

        return $spam;
    }

    /**
     * Indicates if any spam guard failed.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * Informs the checker to only check the provided comment.
     *
     * @param CommentContract $comment The comment to check.
     */
    public function checkSingle(CommentContract $comment)
    {
        $this->checkSingle = true;
        $this->explicitComments[] = $comment;
    }

    /**
     * Resets the spam checker to use the Data Query engine.
     */
    public function resetExplicitComments()
    {
        $this->checkSingle = false;
        $this->explicitComments = [];
    }

}
