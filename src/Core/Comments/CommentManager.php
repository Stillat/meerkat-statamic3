<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedGroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Exceptions\DataQueryException;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Threads\ThreadManagerFactory;

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

    /**
     * Configures a comment as a reply to the provided parent.
     *
     * @param string $parentId The parent comment string identifier.
     * @param CommentContract $comment The child comment instance.
     * @return CommentContract|null
     */
    public function replyTo($parentId, CommentContract $comment)
    {
        $comment->setIsNew(true);
        $comment->setParentId($parentId);

        return $comment;
    }

    /**
     * Attempts to query all comments.
     *
     * @param DataQuery $query The query to apply to all comments.
     * @return GroupedDataSetContract|PagedDataSetContract|PagedGroupedDataSetContract|DataSetContract
     * @throws DataQueryException
     * @throws FilterException
     * @throws InconsistentCompositionException
     */
    public function queryAll(DataQuery $query)
    {
        return $query->getCollection(
            $this->getAll(false),
            CommentResponseGenerator::KEY_API_COMMENT_COLLECTION
        );
    }

    /**
     * Attempts to retrieve all comments.
     *
     * @param bool $withTrashed Indicates if soft-deleted comments should included.
     * @return CommentContract[]
     * @throws FilterException
     * @throws DataQueryException
     * @throws InconsistentCompositionException
     */
    public function getAll($withTrashed = false)
    {
        if (ThreadManagerFactory::hasInstance()) {
            $threads = ThreadManagerFactory::$instance->getAllThreads(false, true);
            $commentsToReturn = [];

            foreach ($threads as $thread) {
                $result = $thread->query(function (DataQuery $q) use ($withTrashed) {
                    return $q->withTrashed($withTrashed);
                });

                $commentsToReturn = array_merge($commentsToReturn, $result->flattenDataset());
            }

            return $commentsToReturn;
        }

        return [];
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
        return $this->removeById($comment->getId());
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
        $result = $this->commentStorageManager->removeById($id);

        return $result->success;
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
