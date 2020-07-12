<?php

namespace Stillat\Meerkat\Core\Threads;

use Exception;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\Core\Storage\Data\ThreadCommentRetriever;

/**
 * Provides utilities/tools for managing Meerkat Comment threads
 *
 * @since 2.0.0
 */
class ThreadManager implements ThreadManagerContract
{

    /**
     * The file-name prefix to use when soft-deleting a thread.
     */
    const THREAD_SOFTDELETED_PREFIX = '_';

    /**
     * The Configuration instance to provide access to the shared file share.
     *
     * @var \Stillat\Meerkat\Core\Configuration
     */
    private $config = null;

    /**
     * The Paths utility instance.
     *
     * @var \Stillat\Meerkat\Core\Storage\Paths
     */
    private $pathManager = null;

    /**
     * The ContextResolverContract instance.
     *
     * @var ContextResolverContract
     */
    private $contextResolver = null;

    /**
     * The ThreadCommentRetriever isntance.
     *
     * @var ThreadCommentRetriever
     */
    private $commentRetriever = null;

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
        ThreadCommentRetriever $commentRetriever,
        ThreadMutationPipelineContract $threadPipeline,
        ThreadStorageManagerContract $streamStorageManager
    ) {
        $this->config = $config;
        $this->contextResolver = $contextResolver;
        $this->commentRetriever = $commentRetriever;
        $this->threadPipeline = $threadPipeline;
        $this->threadStorageManager = $streamStorageManager;

        // Create an instance of Paths using the configuration we just received.
        $this->pathManager = new Paths($this->config);
    }

    /**
     * Performs a simple check to determine if the root thread path is a valid ID.
     *
     * @param string $threadPath The basic path to check.
     * @param boolean $includeTrashed Indicates if Meerkat should locate trashed thread.
     *
     * @return bool
     */
    protected function isValidThreadId($threadPath, $includeTrashed)
    {
        // Length 36 is a normal GUID.
        $threadIdLength = mb_strlen($threadPath);

        if ($threadIdLength == 36) {
            return true;
        }

        return false;
    }

    /**
     * Returns a collection of all thread IDs.
     *
     * @param boolean $includeTrashed Indicates if Meerkat should locate trashed threads.
     *
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
     * @param  boolean $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @return boolean
     */
    public function existsForContext($contextId, $withTrashed)
    {
        return $this->threadStorageManager->existsForContext($contextId, $withTrashed);
    }

    /**
     * Returns all comments attached to the provided thread.
     *
     * @param  ThreadContract $thread
     *
     * @return CommentContract[]
     */
    public function all(ThreadContract $thread)
    {
        return $this->_findCommentsForThreadInstance($thread);
    }

    /**
     * Locates the comments for the provided thread instance.
     *
     * @param  ThreadContract $thread The thread instance to find.
     * @return array
     */
    private function _findCommentsForThreadInstance($thread)
    {
        // Use the comment retriever instance to resolve comments.
        $this->commentRetriever->setThread($thread);
        $comments = $this->commentRetriever->buildHierarchy();

        // Resets the retriever state to prevent accidentally using
        // the resolved comments for an entirely different thread.
        $this->commentRetriever->reset();

        return $comments;
    }

    /**
     * Attempts to locate and return all comments attached
     * to a thread with the provided string identifier.
     *
     * @param  string $id
     *
     * @return CommentContract[]
     */
    public function allForId($id)
    {
        $thread = $this->findById($id, false);

        if ($thread == null) {
            return [];
        }

        return $this->_findCommentsForThreadInstance($thread);
    }

    /**
     * Persists the specified thread to disk.
     *
     * @param  ThreadContract $thread
     *
     * @return ThreadContract
     */
    public function create(ThreadContract $thread)
    {
        return $this->threadStorageManager->save($thread);
    }

    /**
     * Attempts to locate and return a thread for the provided string identifier.
     *
     * @param  string $id The identifier for the thread to look up.
     * @param  boolean $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @param  boolean $includeComments Indicates if Meerkat should pre-load the comments.
     *
     * @return ThreadContract|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        return $this->threadStorageManager->findById($id, $withTrashed, $includeComments);
    }

    /**
     * Resolves the storage path for the provided thread instance.
     *
     * @param  ThreadContract $thread
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
     * @param  string $id
     *
     * @return string
     */
    public function determinePathById($id)
    {
        return $this->threadStorageManager->determineVirtualPathById($id);
    }

    /**
     * Attempts to remove a thread instance.
     *
     * @param  ThreadContract $thread The thread instance.
     * @return boolean
     */
    public function remove(ThreadContract $thread)
    {
        return $this->threadStorageManager->delete($thread);
    }

    /**
     * Attempts to remove a thread by it's identifier.
     *
     * @param  string $id The comment's identifier.
     * @return boolean
     */
    public function removeById($id)
    {
        return $this->threadStorageManager->deleteById($id);
    }

    /**
     * Moves the comments from the source thread to the target thread.
     *
     * @param string $sourceThreadId The identifier of the source thread.
     * @param string $targetThreadId The identifier of the target thread.
     * @return boolean
     */
    public function moveThread($sourceThreadId, $targetThreadId)
    {
        return $this->threadStorageManager->moveThread($sourceThreadId, $targetThreadId);
    }

    
    /**
     * Restores a previously soft-deleted thread.
     *
     * @param  string $threadId The string identifier of the thread.
     * @return boolean
     */
    public function restoreThread($threadId)
    {
        return $this->threadStorageManager->restoreThread($threadId);
    }
}
