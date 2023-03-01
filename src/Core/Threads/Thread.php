<?php

namespace Stillat\Meerkat\Core\Threads;

use JsonSerializable;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\GroupedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Data\PagedDataSetContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Data\Converters\BaseCollectionConverter;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\DataQueryFactory;
use Stillat\Meerkat\Core\Data\DataSet;
use Stillat\Meerkat\Core\Data\Retrievers\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\Exceptions\DataQueryException;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;
use Stillat\Meerkat\Core\Threads\StaticApi\ProvidesDiscovery;

/**
 * Class Thread
 *
 * The base Meerkat Thread implementation
 *
 * A Thread represents a collection of comments related
 * to a single context. A context is any data object;
 * common examples include blog posts and photos.
 *
 * @since 2.0.0
 */
class Thread implements ThreadContract, JsonSerializable
{
    use DataObject, ProvidesDiscovery;

    /**
     * The storage path for the thread.
     *
     * @var string
     */
    public $path = '';

    /**
     * The thread's context, if any.
     *
     * @var ThreadContextContract|null
     */
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
     * The total number of all comments in the thread.
     *
     * @var int
     */
    private $totalCommentCount = 0;

    /**
     * The total (cached) number of root level comments.
     *
     * @var int
     */
    private $totalRootLevelCommentCount = 0;

    /**
     * Indicates whether the current thread was soft-deleted.
     *
     * @var bool
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
     * The thread's hierarchy.
     *
     * @var ThreadHierarchy|null
     */
    private $hierarchy = null;

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
     * Sets if the thread is usable.
     *
     * @param  bool  $isUsable The threads usability status.
     */
    public function setIsUsable($isUsable)
    {
        $this->isUsable = $isUsable;
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
     * Sets the storage path.
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Sets the ID for the current thread.
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id)
    {
        $this->threadId = $id;
        $this->setDataAttribute(ThreadContract::KEY_ID, $id);
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
     * Sets the thread's meta data.
     *
     * @param  ThreadMetaData  $metaData The meta data.
     */
    public function setMetaData(ThreadMetaData $metaData)
    {
        $this->metaData = $metaData;
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
     * @param  string  $id
     * @return void
     */
    public function setContextId($id)
    {
        $this->contextId = $id;
        $this->setDataAttribute(ThreadContract::KEY_CONTEXT_ID, $id);
    }

    /**
     * Sets the comments for the current thread.
     *
     * @param  CommentContract[]  $comments The comments to set on the thread.
     * @return void
     */
    public function setComments($comments)
    {
        $this->hierarchy->setComments($comments);
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
     * @param  int  $count The total number of comments in the thread.
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
     * @param  int  $count The total number of root-level comments in the thread.
     * @return void
     */
    public function setRootCommentCount($count)
    {
        $this->totalRootLevelCommentCount = $count;
    }

    /**
     * Returns a value indicating if the current thread was soft deleted.
     *
     * @return bool
     */
    public function isTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * Sets whether or not the Meerkat thread was soft-deleted.
     *
     * @param  bool  $isTrashed A value indicating if the thread wa soft-deleted.
     * @return void
     */
    public function setIsTrashed($isTrashed)
    {
        $this->isTrashed = $isTrashed;
    }

    /**
     * Attempts to remove the current thread instance.
     *
     * @return bool
     */
    public function delete()
    {
        if (ThreadManagerFactory::hasInstance() == false) {
            return false;
        }

        return ThreadManagerFactory::$instance->removeById($this->getId());
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
     * Converts the thread's comments into an array; sets the comment reply property to the provided name
     *
     * @param  string  $repliesName The replies data property to use.
     * @return array
     *
     * @throws InconsistentCompositionException
     */
    public function getCommentCollection($repliesName)
    {
        $comments = $this->hierarchy->getComments();

        return BaseCollectionConverter::make()->convert($comments, $repliesName);
    }

    /**
     * Attempts to attach the provided comment to the thread.
     *
     * @param  CommentContract  $comment The comment to attach to the thread.
     * @return bool
     */
    public function attachNewComment(CommentContract $comment)
    {
        $comment->setIsNew(true);
        $comment->setThreadId($this->getId());

        return $comment->save();
    }

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
    public function query(callable $builder)
    {
        $queryInstance = DataQueryFactory::newQuery();

        if ($queryInstance === null) {
            throw new InconsistentCompositionException('The DataQueryFactory shared instance must be set in order to use the static APIs.');
        }

        if ($queryInstance === null) {
            throw new DataQueryException('Static DataQueryFactory implementations must not return a NULL builder.');
        }

        $queryInstance = $builder($queryInstance);

        if ($queryInstance === null || ($queryInstance instanceof DataQuery) === false) {
            throw new DataQueryException('Builder callbacks must return an instance of DataQuery.');
        }

        $runtimeContext = new RuntimeContext();
        $runtimeContext->context = $this->getContext();

        return $queryInstance->withContext($runtimeContext)->get($this->getComments());
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
     *
     * @return void
     */
    public function setContext(ThreadContextContract $context)
    {
        $this->context = $context;
    }

    /**
     * Gets the comments for the current thread.
     *
     * @return CommentContract[]
     */
    public function getComments()
    {
        return $this->hierarchy->getComments();
    }

    /**
     * Returns the data to serialize as JSON.
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->getDataAttributes();
    }

    /**
     * Attempts to retrieve the participants for the thread.
     *
     * @return AuthorContract[]
     */
    public function getParticipants()
    {
        $participants = [];

        $threadHierarchy = $this->getHierarchy();

        if ($threadHierarchy !== null) {
            $comments = $threadHierarchy->getComments();

            if ($comments !== null && is_array($comments)) {
                $participants = CommentAuthorRetriever::getAuthors($comments);
            }
        }

        return $participants;
    }

    /**
     * Gets the thread's hierarchy.
     *
     * @return ThreadHierarchy|null
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }

    /**
     * Sets the thread's hierarchy.
     *
     * @param  ThreadHierarchy  $hierarchy The thread's structure.
     * @return void
     */
    public function setHierarchy(ThreadHierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
        $this->totalCommentCount = $hierarchy->getTotalCommentCount();
        $this->totalRootLevelCommentCount = $hierarchy->getRootLevelCommentCount();
    }

    /**
     * Attempts to retrieve the participants for the thread.
     *
     * @param  string[]  $commentIds The comment identifiers.
     * @return AuthorContract[]
     */
    public function getParticipantsFor($commentIds)
    {
        $participants = [];

        $threadHierarchy = $this->getHierarchy();

        if ($threadHierarchy !== null) {
            $comments = $threadHierarchy->getComments();

            $comments = array_filter($comments, function (CommentContract $comment) use ($commentIds) {
                return in_array($comment->getId(), $commentIds);
            });

            if ($comments !== null && is_array($comments)) {
                $participants = CommentAuthorRetriever::getAuthors($comments);
            }
        }

        return $participants;
    }
}
