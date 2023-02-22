<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use Stillat\Meerkat\Core\Comments\AffectsCommentsResult;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Exceptions\FilterException;

/**
 * Interface CommentManagerContract
 *
 * Defines a consistent API for managing Meerkat comments
 *
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

    /**
     * Attempts to retrieve all comments.
     *
     * @param  bool  $withTrashed Indicates if soft-deleted threads should be included.
     * @return CommentContract[]
     */
    public function getAll($withTrashed = false);

    /**
     * Attempts to query all comments.
     *
     * @param  DataQuery  $query The query to apply to all comments.
     * @return GroupedDataSetContract|PagedDataSetContract|PagedGroupedDataSetContract|DataSetContract
     *
     * @throws FilterException
     */
    public function queryAll(DataQuery $query);

    /**
     * Configures the provided comment to be a reply to the specified parent.
     *
     * @param  string  $parentId The parent comment's string identifier.
     * @param  CommentContract  $comment The comment to attach as a reply.
     * @return CommentContract
     */
    public function replyTo($parentId, CommentContract $comment);

    /**
     * Saves the provided comment as a reply to the specified parent.
     *
     * @param  string  $parentId The parent comment's string identifier.
     * @param  CommentContract  $comment The comment to attach as a reply.
     * @return bool
     */
    public function saveReplyTo($parentId, CommentContract $comment);

    /**
     * Attempts to locate a comment by it's string identifier.
     *
     * @param  string  $id
     * @return CommentContract|null
     */
    public function findById($id);

    /**
     * Attempts to remove the provided comment completely.
     *
     * @param  CommentContract  $comment
     * @return AffectsCommentsResult
     */
    public function remove($comment);

    /**
     * Attempts to locate and remove the comment by it's string identifier.
     *
     * @param  string  $id
     * @return AffectsCommentsResult
     */
    public function removeById($id);

    /**
     * Resolves the storage path for the provided comment.
     *
     * @param  CommentContract  $comment
     * @return string
     */
    public function determinePath($comment);

    /**
     * Resolves the storage path for a comment with the provided string identifier.
     *
     * @param  string  $id
     * @return string
     */
    public function determinePathById($id);
}
