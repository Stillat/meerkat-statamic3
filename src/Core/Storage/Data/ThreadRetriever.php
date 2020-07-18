<?php

namespace Stillat\Meerkat\Core\Storage\Data;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

/**
 * Class ThreadRetriever
 *
 * A wrapper utility to retrieve all threads
 *
 * To have greater control over locating specific threads, you
 * should use the ThreadManagerContract.php implementation.
 *
 * @package Stillat\Meerkat\Core\Storage\Data
 * @since 2.0.0
 */
class ThreadRetriever
{

    /**
     * The Configuration instance to provide access to the shared file share.
     *
     * @var Configuration
     */
    private $config = null;

    /**
     * The implementor's context resolver.
     *
     * @var ContextResolverContract
     */
    private $contextResolver = null;

    /**
     * The comment record retriever instance.
     *
     * @var ThreadCommentRetriever
     */
    private $threadCommentRetriever = null;

    /**
     * The stream storage manager instance.
     *
     * @var ThreadStorageManagerContract
     */
    private $streamStorageManager = null;

    public function __construct(
        Configuration $config,
        ContextResolverContract $contextResolver,
        ThreadCommentRetriever $threadRetriever,
        ThreadStorageManagerContract $streamStorage)
    {
        $this->config = $config;
        $this->contextResolver = $contextResolver;
        $this->threadCommentRetriever = $threadRetriever;
        $this->streamStorageManager = $streamStorage;
    }

    /**
     * Returns all thread directories.
     *
     * @param  boolean $withTrashed Indicates whether or not soft-deleted threads should be included in the result set.
     * @param  boolean $includeComments Indicates whether or not comments should be included in the result set.
     * @return ThreadContract[]
     */
    public function getThreads($withTrashed, $includeComments)
    {
        $threads = $this->streamStorageManager->getAllThreads($withTrashed);

        foreach ($threads as $thread) {
            if ($includeComments) {
                $this->threadCommentRetriever->setThread($thread);

                $thread->setComments($this->threadCommentRetriever->buildHierarchy());
                // Set some cached statistics.
                $thread->setTotalCommentCount($this->threadCommentRetriever->getAllCommentsCount());
                $thread->setRootCommentCount($this->threadCommentRetriever->getRootCommentsCount());
            } else {
                $thread->setComments([]);
            }
        }

        return $threads;
    }

}
