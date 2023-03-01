<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Serializable;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\StorableContract;
use Stillat\Meerkat\Core\Data\DataSet;
use Stillat\Meerkat\Core\Exceptions\DataQueryException;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;

/**
 * Interface ThreadContract
 *
 * Defines a standardized comment thread structure
 *
 * @since 2.0.0
 */
interface ThreadContract extends DataObjectContract, StorableContract, Serializable
{
    const LEGACY_SOFT_DELETE_PREFIX = '_';

    const KEY_ID = 'id';

    const KEY_CONTEXT_ID = 'context_id';

    const KEY_PATH = 'path';

    const KEY_DIRNAME = 'dirname';

    const KEY_TYPE = 'type';

    const KEY_TYPE_FILE = 'file';

    const KEY_SHARE_COMMENT_CONTEXT = 'meerkat_share_comments';

    /**
     * Returns the string identifier for the current thread.
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the ID for the current thread.
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id);

    /**
     * Attempts to locate and return the thread's context.
     *
     * @return ThreadContextContract|null
     */
    public function getContext();

    /**
     * Sets the thread's context.
     *
     *
     * @return void
     */
    public function setContext(ThreadContextContract $context);

    /**
     * Attempts to locate and return the thread context string identifier.
     *
     * @return string
     */
    public function getContextId();

    /**
     * Sets if the thread is usable, based on it's persistence state.
     *
     * @param  bool  $isUsable If the thread is usable.
     * @return void
     */
    public function setIsUsable($isUsable);

    /**
     * Returns a value indicating if the thread is usable.
     *
     * @return bool
     */
    public function getIsUsable();

    /**
     * Sets the context identifier for the thread.
     *
     * @param  string  $id
     * @return void
     */
    public function setContextId($id);

    /**
     * Gets the comments for the current thread.
     *
     * @return CommentContract[]
     */
    public function getComments();

    /**
     * Sets the comments for the current thread.
     *
     * @param  CommentContract[]  $comments The comments to set on the thread.
     * @return void
     */
    public function setComments($comments);

    /**
     * Gets the total number of comments in the thread.
     *
     * @return int
     */
    public function getTotalCommentCount();

    /**
     * Sets the total number of comments in the thread.
     *
     * @param  int  $count The total number of comments in the thread.
     * @return void
     */
    public function setTotalCommentCount($count);

    /**
     * Returns the total number of root comments in the thread.
     *
     * @return int
     */
    public function getRootCommentCount();

    /**
     * Sets the total number of root comment counts.
     *
     * @param  int  $count The total number of root-level comments in the thread.
     * @return void
     */
    public function setRootCommentCount($count);

    /**
     * Returns a value indicating if the current thread was soft deleted.
     *
     * @return bool
     */
    public function isTrashed();

    /**
     * Sets whether or not the Meerkat thread was soft-deleted.
     *
     * @param  bool  $isTrashed A value indicating if the thread wa soft-deleted.
     * @return void
     */
    public function setIsTrashed($isTrashed);

    /**
     * Sets the thread's meta data.
     *
     * @param  ThreadMetaData  $metaData The meta data.
     * @return void
     */
    public function setMetaData(ThreadMetaData $metaData);

    /**
     * Gets the thread's meta data, if available.
     *
     * @return ThreadMetaData|null
     */
    public function getMetaData();

    /**
     * Sets the thread's hierarchy.
     *
     * @param  ThreadHierarchy  $hierarchy The thread's structure.
     * @return void
     */
    public function setHierarchy(ThreadHierarchy $hierarchy);

    /**
     * Gets the thread's hierarchy.
     *
     * @return ThreadHierarchy|null
     */
    public function getHierarchy();

    /**
     * Converts the thread's comments into an array; sets the comment reply property to the provided name
     *
     * @param  string  $repliesName The replies data property to use.
     * @return array
     */
    public function getCommentCollection($repliesName);

    /**
     * Saves the provided comment to the thread.
     *
     * @param  CommentContract  $comment The comment to attach to the thread.
     * @return bool
     */
    public function attachNewComment(CommentContract $comment);

    /**
     * Attempts to retrieve the participants for the thread.
     *
     * @return AuthorContract[]
     */
    public function getParticipants();

    /**
     * Attempts to retrieve the participants for the thread.
     *
     * @param  string[]  $commentIds The comment identifiers.
     * @return AuthorContract[]
     */
    public function getParticipantsFor($commentIds);

    /**
     * Queries the thread's comments.
     *
     * @param  callable  $builder A callback to modify the query builder.
     * @return array|mixed|DataSetContract|GroupedDataSetContract|PagedDataSetContract|DataSet
     *
     * @throws DataQueryException
     * @throws InconsistentCompositionException
     * @throws FilterException
     */
    public function query(callable $builder);
}
