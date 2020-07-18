<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;

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
        // TODO: Implement findById() method.
        return null;
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