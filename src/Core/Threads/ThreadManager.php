<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\DateUtilities;

/**
 * Class ThreadManager
 *
 * Provides utilities/tools for managing Meerkat Comment threads.
 *
 * @since 2.0.0
 */
class ThreadManager implements ThreadManagerContract
{
    /**
     * The Configuration instance to provide access to the shared file share.
     *
     * @var Configuration
     */
    private $config = null;

    /**
     * The Paths utility instance.
     *
     * @var Paths
     */
    private $pathManager = null;

    /**
     * The ContextResolverContract instance.
     *
     * @var ContextResolverContract
     */
    private $contextResolver = null;

    /**
     * The ThreadMutationPipelineContract implementation.
     *
     * @var ThreadMutationPipelineContract
     */
    private $threadPipeline = null;

    /**
     * The stream storage manager instance.
     *
     * @var ThreadStorageManagerContract
     */
    private $threadStorageManager = null;

    public function __construct(
        Configuration $config,
        ContextResolverContract $contextResolver,
        ThreadMutationPipelineContract $threadPipeline,
        ThreadStorageManagerContract $streamStorageManager
    ) {
        $this->config = $config;
        $this->contextResolver = $contextResolver;
        $this->threadPipeline = $threadPipeline;
        $this->threadStorageManager = $streamStorageManager;

        // Create an instance of Paths using the configuration we just received.
        $this->pathManager = new Paths($this->config);
    }

    /**
     * Gets the thread storage manager implementation instance.
     *
     * @return ThreadStorageManagerContract
     */
    public function getStorageManager()
    {
        return $this->threadStorageManager;
    }

    /**
     * Attempts to locate all threads.
     *
     * @param  bool  $includeTrashed Whether to include soft-deleted threads.
     * @param  bool  $includeComments Whether to include comments.
     * @return ThreadContract[]
     */
    public function getAllThreads($includeTrashed = false, $includeComments = false)
    {
        return $this->threadStorageManager->getAllThreads($includeTrashed, $includeComments);
    }

    /**
     * Returns a collection of all thread IDs.
     *
     * @param  bool  $includeTrashed Indicates if Meerkat should locate trashed threads.
     * @return array
     */
    public function getAllThreadIds($includeTrashed = false)
    {
        return $this->threadStorageManager->getAllThreadIds($includeTrashed);
    }

    /**
     * Returns a value indicating if a thread exists for the provided context identifier.
     *
     * @param  string  $contextId The context's string identifier.
     * @param  bool  $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @return bool
     */
    public function existsForContext($contextId, $withTrashed)
    {
        return $this->threadStorageManager->existsForContext($contextId, $withTrashed);
    }

    /**
     * Attempts to locate and return a thread for the provided string identifier.
     *
     * @param  string  $id The identifier for the thread to look up.
     * @param  bool  $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @param  bool  $includeComments Indicates if Meerkat should pre-load the comments.
     * @return ThreadContract|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        return $this->threadStorageManager->findById($id, $withTrashed, $includeComments);
    }

    /**
     * Persists the specified thread to disk.
     *
     *
     * @return ThreadContract
     */
    public function create(ThreadContract $thread)
    {
        $context = $thread->getContext();

        if ($context === null) {
            $context = $this->contextResolver->findById($thread->getId());
        }

        $this->threadStorageManager->createForContext($context);

        return $thread;
    }

    /**
     * Resolves the storage path for the provided thread instance.
     *
     *
     * @return string
     */
    public function determinePath(ThreadContract $thread)
    {
        return $this->determinePathById($thread->getId());
    }

    /**
     * Resolves the storage path for the provided thread string identifier.
     *
     * @param  string  $id
     * @return string
     */
    public function determinePathById($id)
    {
        return $this->threadStorageManager->determineVirtualPathById($id);
    }

    /**
     * Attempts to remove a thread instance.
     *
     * @param  ThreadContract  $thread The thread instance.
     * @return bool
     */
    public function remove(ThreadContract $thread)
    {
        return $this->threadStorageManager->delete($thread);
    }

    /**
     * Attempts to remove a thread by it's identifier.
     *
     * @param  string  $id The comment's identifier.
     * @return bool
     */
    public function removeById($id)
    {
        return $this->threadStorageManager->deleteById($id);
    }

    /**
     * Moves the comments from the source thread to the target thread.
     *
     * @param  string  $sourceThreadId The identifier of the source thread.
     * @param  string  $targetThreadId The identifier of the target thread.
     * @return bool
     */
    public function moveThread($sourceThreadId, $targetThreadId)
    {
        return $this->threadStorageManager->moveThread($sourceThreadId, $targetThreadId);
    }

    /**
     * Restores a previously soft-deleted thread.
     *
     * @param  string  $threadId The string identifier of the thread.
     * @return bool
     */
    public function restoreThread($threadId)
    {
        return $this->threadStorageManager->restoreThread($threadId);
    }

    /**
     * Determines if new comment submissions are allowed for the requested context identifier.
     *
     * @param  string  $contextId The context's identifier.
     * @return bool
     */
    public function areCommentsEnabledForContext($contextId)
    {
        if ($this->config->commentsCanBeDisabled() === false) {
            return true;
        }

        // We will use the context resolver directly. If there
        // are no comments stored for the thread, the exists
        // methods and utilities will always return false.
        $context = $this->contextResolver->findById($contextId);

        $daysBetween = DateUtilities::daysBetween(time(), $context->getCreatedUtcTimestamp());

        if ($daysBetween > $this->config->disableCommentsAfterDays) {
            return false;
        }

        return true;
    }
}
