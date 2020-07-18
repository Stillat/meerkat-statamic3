<?php

namespace Stillat\Meerkat\Core\Threads;

use JsonSerializable;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class Thread
 *
 * The base Meerkat Thread implementation
 *
 * A Thread represents a collection of comments related
 * to a single context. A context is any data object;
 * common examples include blog posts and photos.
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class Thread implements ThreadContract, JsonSerializable
{
    use DataObject;

    private $context = null;

    /**
     * The ID for the thread.
     *
     * In most situations, this will be the same as the thread's relative path.
     *
     * @var string
     */
    private $threadId = '';

    /**
     * The thread context string identifier.
     *
     * @var string
     */
    private $contextId = '';

    /**
     * The relative storage path for the thread.
     *
     * @var string
     */
    public $path = '';

    /**
     * The comments on the thread.
     *
     * @var array
     */
    private $comments = [];

    /**
     * The total number of all comments in the thread.
     *
     * @var integer
     */
    private $totalCommentCount = 0;

    /**
     * The total (cached) number of root level comments.
     *
     * @var integer
     */
    private $totalRootLevelCommentCount = 0;

    /**
     * Indicates whether the current thread was soft-deleted.
     *
     * @var boolean
     */
    private $isTrashed = false;

    /**
     * The thread's meta data, if any.
     *
     * @var ThreadMetaData|null
     */
    private $metaData = null;

    /**
     * Indicates if this thread is considered "usable".
     *
     * A run-time thread that becomes "un-usable" generally
     * occurs when a thread has been deleted and the
     * run-time collection has not updated yet.
     *
     * @var bool
     */
    private $isUsable = false;

    /**
     * Sets if the thread is usable.
     *
     * @param bool $isUsable The threads usability status.
     */
    public function setIsUsable($isUsable)
    {
        $this->isUsable = $isUsable;
    }

    /**
     * Gets a value indicating if the thread is currently usable.
     *
     * Unusable threads should be rejected for most operations.
     *
     * @return bool
     */
    public function getIsUsable()
    {
        return $this->isUsable;
    }

    /**
     * Sets the storage path.
     *
     * @param  string $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Gets the storage path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the ID for the thread, as represented on disk.
     *
     * @return string
     */
    public function getId()
    {
        return $this->threadId;
    }

    /**
     * Sets the ID for the current thread.
     *
     * @param  string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->threadId = $id;
        $this->setDataAttribute(ThreadContract::KEY_ID, $id);
    }

    /**
     * Sets the thread's meta data.
     *
     * @param ThreadMetaData $metaData The meta data.
     */
    public function setMetaData(ThreadMetaData $metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Gets the thread's meta data, if available.
     *
     * @return ThreadMetaData|null
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Attempts to locate and return the thread's context.
     *
     * @return ThreadContextContract|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the thread's context.
     *
     * @param ThreadContextContract $context
     *
     * @return void
     */
    public function setContext(ThreadContextContract $context)
    {
        $this->context = $context;
    }

    /**
     * Attempts to locate and return the thread context string identifier.
     *
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * Sets the context identifier for the thread.
     *
     * @param  string $id
     *
     * @return void
     */
    public function setContextId($id)
    {
        $this->contextId = $id;
        $this->setDataAttribute(ThreadContract::KEY_CONTEXT_ID, $id);
    }

    /**
     * Gets the comments for the current thread.
     *
     * @return CommentContract[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Sets the comments for the current thread.
     *
     * @param  CommentContract[] $comments The comments to set on the thread.
     * @return void
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Gets the total number of comments in the thread.
     *
     * @return int
     */
    public function getTotalCommentCount()
    {
        return $this->totalCommentCount;
    }

    /**
     * Sets the total number of comments in the thread.
     *
     * @param  int $count The total number of comments in the thread.
     * @return void
     */
    public function setTotalCommentCount($count)
    {
        $this->totalCommentCount = $count;
    }

    /**
     * Returns the total number of root comments in the thread.
     *
     * @return int
     */
    public function getRootCommentCount()
    {
        return $this->totalRootLevelCommentCount;
    }

    /**
     * Sets the total number of root comment counts.
     *
     * @param  int $count The total number of root-level comments in the thread.
     * @return void
     */
    public function setRootCommentCount($count)
    {
        $this->totalRootLevelCommentCount = $count;
    }

    /**
     * Returns a value indicating if the current thread was soft deleted.
     *
     * @return boolean
     */
    public function isTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * Sets whether or not the Meerkat thread was soft-deleted.
     *
     * @param  bool $isTrashed A value indicating if the thread wa soft-deleted.
     * @return void
     */
    public function setIsTrashed($isTrashed)
    {
        $this->isTrashed = $isTrashed;
    }

    /**
     * Attempts to remove the current thread instance.
     *
     * @return boolean
     */
    public function delete()
    {
        if (ThreadManagerFactory::hasInstance() == false) {
            return false;
        }

        return ThreadManagerFactory::$instance->removeById($this->getId());
    }

    public function jsonSerialize()
    {
        return $this->getDataAttributes();
    }

}
