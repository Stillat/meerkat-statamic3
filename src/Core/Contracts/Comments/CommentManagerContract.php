<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;

/**
 * Interface CommentManagerContract
 *
 * Defines a consistent API for managing Meerkat comments
 *
 * @package Stillat\Meerkat\Core\Contracts\Commentsg
 * @since 2.0.0
 */
interface CommentManagerContract
{

    /**
     * Gets the comment storage manager implementation.
     *
     * @return CommentStorageManagerContract
     */
    public function getStorageManager();

    public function getAll($withTrashed = false);

    /**
     * Attempts to locate a comment by it's string identifier.
     *
     * @param  string $id
     *
     * @return CommentContract|null
     */
    public function findById($id);

    /**
     * Attempts to remove the provided comment completely.
     *
     * @param  CommentContract $comment
     *
     * @return boolean
     */
    public function remove($comment);

    /**
     * Attempts to locate and remove the comment by it's string identifier.
     *
     * @param  string  $id
     *
     * @return boolean
     */
    public function removeById($id);

    /**
     * Resolves the storage path for the provided comment.
     *
     * @param  CommentContract $comment
     *
     * @return string
     */
    public function determinePath($comment);

    /**
     * Resolves the storage path for a comment with the provided string identifier.
     *
     * @param  string $id
     *
     * @return string
     */
    public function determinePathById($id);
}
