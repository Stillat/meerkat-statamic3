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
        // TODO: Implement determinePath() method.
        return null;
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
        // TODO: Implement determinePathById() method.
        return null;
    }
}