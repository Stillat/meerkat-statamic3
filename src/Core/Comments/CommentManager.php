<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;

/**
 * Class CommentManager
 *
 * Provides a consistent API for managing comments.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class CommentManager implements CommentManagerContract
{

    /**
     * The storage manager implementation instance.
     *
     * @var CommentStorageManagerContract|null
     */
    protected $commentStorageManager = null;

    public function __construct(CommentStorageManagerContract $storageManager)
    {
        $this->commentStorageManager = $storageManager;
    }

    /**
     * Gets the comment storage manager implementation.
     *
     * @return CommentStorageManagerContract
     */
    public function getStorageManager()
    {
        return $this->commentStorageManager;
    }


    public function replyTo($parentId, CommentContract $comment)
    {
        $comment->setIsNew(true);
        $comment->setParentId($parentId);

        return $comment;
    }

    /**
     * Saves a new reply for the provided parent comment.
     *
     * @param string $parentId The parent comment string identifier.
     * @param CommentContract $comment The comment to save as a reply.
     * @return bool
     */
    public function saveReplyTo($parentId, CommentContract $comment)
    {
        $commentToSave = $this->replyTo($parentId, $comment);

        return $this->commentStorageManager->save($commentToSave);
    }

    public function getAll($withTrashed = false)
    {
        // TODO: Implement getAll() method.
    }

    /**
     * Attempts to locate a comment by it's string identifier.
     *
     * @param string $id
     *
     * @return CommentContract|null
     */
    public function findById($id)
    {
        return $this->commentStorageManager->findById($id);
    }

    /**
     * Attempts to remove the provided comment completely.
     *
     * @param CommentContract $comment
     *
     * @return boolean
     */
    public function remove($comment)
    {
        // TODO: Implement remove() method.
        return null;
    }

    /**
     * Attempts to locate and remove the comment by it's string identifier.
     *
     * @param string $id
     *
     * @return boolean
     */
    public function removeById($id)
    {
        // TODO: Implement removeById() method.
        return null;
    }

    /**
     * Resolves the storage path for the provided comment.
     *
     * @param CommentContract $comment
     *
     * @return string
     */
    public function determinePath($comment)
    {
        return $this->determinePathById($comment->getId());
    }

    /**
     * Resolves the storage path for a comment with the provided string identifier.
     *
     * @param string $id
     *
     * @return string
     */
    public function determinePathById($id)
    {
        return $this->commentStorageManager->getPathById($id);
    }
}