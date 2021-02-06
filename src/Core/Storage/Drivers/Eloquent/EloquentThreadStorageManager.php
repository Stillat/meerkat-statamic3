<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Exception;
use Illuminate\Support\Facades\DB;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\DataQueryFactory;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\ParserException;
use Stillat\Meerkat\Core\Identity\IdentityManagerFactory;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models\DatabaseThread;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;
use Stillat\Meerkat\Core\Threads\ThreadRemovalEventArgs;
use Stillat\Meerkat\Core\ValidationResult;

class EloquentThreadStorageManager implements ThreadStorageManagerContract
{

    /**
     * A collection of previously resolved thread meta-data.
     *
     * Memoization for the following:
     *  - getMetaData()
     *
     * @var array
     */
    protected static $metaResolverCache = [];

    /**
     * The Meerkat Core configuration container.
     *
     * @var Configuration
     */
    private $meerkatConfiguration;

    /**
     * The local system path where comments are stored.
     *
     * In the context of a database driver, this is used to populate the virtual_path fields.
     *
     * @var string|string[]
     */
    private $storagePath = '';

    /**
     * The context resolver implementation instance.
     *
     * @var ContextResolverContract The instance.
     */
    private $contextResolver;

    /**
     * Internal override to make some shared functions work between driver types.
     *
     * @var bool
     */
    private $canUseDirectory = true;

    /**
     * The comment storage manager implementation instance.
     *
     * @var CommentStorageManagerContract|null
     */
    private $commentStorageManager = null;

    /**
     * The ThreadMutationPipelineContract implementation instance.
     *
     * @var ThreadMutationPipelineContract
     */
    private $threadPipeline = null;

    public function __construct(
        Configuration $config,
        ContextResolverContract $contextResolver,
        CommentStorageManagerContract $commentStorageManager,
        ThreadMutationPipelineContract $threadPipeline)
    {
        $this->meerkatConfiguration = $config;
        $this->contextResolver = $contextResolver;
        $this->threadPipeline = $threadPipeline;
        $this->commentStorageManager = $commentStorageManager;
    }

    /**
     * Validates the storage driver configuration.
     *
     * @return ValidationResult
     */
    public function validate()
    {
        // TODO: Implement validate() method.
    }

    /**
     * Attempts to retrieve all comment threads.
     *
     * @param bool $withTrashed Whether to include soft deleted threads.
     * @param bool $withComments Whether or not to include comments.
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false, $withComments = false)
    {
        /** @var DatabaseThread[] $databaseThreads */
        $databaseThreads = [];

        if ($withTrashed === true) {
            $databaseThreads = DatabaseThread::withTrashed()->all();
        } else {
            $databaseThreads = DatabaseThread::all();
        }

        $threadsToReturn = [];

        foreach ($databaseThreads as $thread) {
            $newThread = $this->materializeThreadFromDatabaseRecord($thread, $withComments);

            $threadsToReturn[] = $newThread;
        }

        return $threadsToReturn;
    }

    /**
     * @param DatabaseThread|null $threadInstance
     * @param bool $includeComments
     * @return Thread|null
     */
    private function materializeThreadFromDatabaseRecord($threadInstance, $includeComments)
    {
        if ($threadInstance === null) {
            return null;
        }

        $metaData = $this->getMetaData($threadInstance);

        $newThread = new Thread();

        $newThread->setMetaData($metaData);
        $newThread->setId($threadInstance->context_id);
        $newThread->setContextId($threadInstance->context_id);

        $threadContext = $this->contextResolver->findById($threadInstance->context_id);

        if ($threadContext !== null) {
            $newThread->setContext($threadContext);
            $newThread->setIsUsable(true);
        } else {
            $newThread->setIsUsable(false);
            LocalErrorCodeRepository::log(ErrorLog::warning(Errors::THREAD_CONTEXT_NOT_FOUND, $threadInstance->context_id));
        }

        if ($includeComments) {
            $newThread->setHierarchy($this->getAllCommentsById($threadInstance->context_id));
        }

        $newThread->path = $threadInstance->virtual_path;

        return $newThread;
    }

    /**
     * @param DatabaseThread $databaseThread The thread record.
     * @return ThreadMetaData|null
     */
    private function getMetaData($databaseThread)
    {
        if ($databaseThread === null) {
            return null;
        }

        $contextId = $databaseThread->context_id;

        if (array_key_exists($contextId, self::$metaResolverCache)) {
            return self::$metaResolverCache[$contextId];
        }

        $attributes = json_decode($databaseThread->meta_data, true);

        $metaData = new ThreadMetaData();

        $metaData->setCreatedOn($databaseThread->created_at->timestamp);
        $metaData->setIsTrashed($databaseThread->trashed());
        $metaData->setDataAttributes($attributes);

        self::$metaResolverCache[$contextId] = $metaData;

        return $metaData;
    }

    /**
     * Gets all the comments for the provided thread identifier.
     *
     * @param string $threadId The thread's string identifier.
     * @return ThreadHierarchy
     */
    public function getAllCommentsById($threadId)
    {
        return $this->commentStorageManager->getCommentsForThreadId($threadId);
    }

    /**
     * Returns a value indicating if a thread exists with the provided identifier.
     *
     * @param string $contextId The thread's identifier.
     * @param bool $withTrashed Indicates if soft-deleted threads are considered.
     * @return bool
     */
    public function existsForContext($contextId, $withTrashed)
    {
        $threadCandidate = $this->locateThreadCandidate($contextId, $withTrashed);

        if ($threadCandidate === null) {
            return false;
        }

        return true;
    }

    /**
     * Locates a database thread with the provided details.
     *
     * @param string $contextId The thread's context identifier.
     * @param bool $withTrashed Whether to include soft-deleted threads.
     * @return DatabaseThread|null
     */
    private function locateThreadCandidate($contextId, $withTrashed)
    {
        $findQuery = DatabaseThread::where('context_id', $contextId);

        if ($withTrashed === true) {
            $findQuery = $findQuery->withTrashed();
        }

        return $findQuery->first();
    }

    /**
     * Retrieves the comments for the provided thread.
     *
     * @param ThreadContract $thread
     * @return ThreadHierarchy
     */
    public function getAllComments(ThreadContract $thread)
    {
        return $this->getAllCommentsById($thread->getId());
    }

    /**
     * Returns all comments across all threads, for the currently authenticated user.
     *
     * @return CommentContract[]
     * @throws FilterException
     * @throws ParserException
     */
    public function getAllSystemCommentsForCurrentUser()
    {
        $builder = DataQueryFactory::newQuery();

        if ($builder === null) {
            return [];
        }

        $builder->withContext(new RuntimeContext());
        $identityManager = IdentityManagerFactory::$instance;
        $currentIdentity = $identityManager->getIdentityContext();

        if ($currentIdentity === null) {
            return [];
        }

        $builder->where(AuthorContract::AUTHENTICATED_USER_ID, '=', $currentIdentity->getId());

        return $builder->get($this->getAllSystemComments())->flattenDataset();
    }

    /**
     * Returns all comments across all threads.
     *
     * @return CommentContract[]
     */
    public function getAllSystemComments()
    {
        $threads = $this->getAllThreadIds();
        $comments = [];

        foreach ($threads as $thread) {
            $threadHierarchy = $this->getAllCommentsById($thread);

            if ($threadHierarchy !== null) {
                $threadComments = $threadHierarchy->getComments();

                foreach ($threadComments as $comment) {
                    $comments[$comment->getId()] = $comment;
                }
            }
        }

        return $comments;
    }

    /**
     * Returns the identifiers of all currently stored threads.
     *
     * @param bool $includeTrashed Indicates if soft-deleted threads should be included.
     * @return array
     */
    public function getAllThreadIds($includeTrashed = false)
    {
        $query = DB::table('meerkat_threads')->select('context_id');

        if ($includeTrashed === false) {
            $query = $query->whereNull('deleted_at');
        }

        return $query->get()->pluck('context_id')
            ->values()->toArray();
    }

    /**
     * Returns all comments across all threads, for the provided user.
     *
     * @param string $userId The user's identifier.
     * @return CommentContract[]
     * @throws FilterException
     * @throws ParserException
     */
    public function getAllCommentsForUserId($userId)
    {
        $builder = DataQueryFactory::newQuery();

        if ($builder === null) {
            return [];
        }

        $builder->withContext(new RuntimeContext());
        $builder->where(AuthorContract::AUTHENTICATED_USER_ID, '=', $userId);

        return $builder->get($this->getAllSystemComments())->flattenDataset();
    }

    /**
     * Queries all system comments using the provided query builder.
     *
     * @param callable $builderCallback The builder callback.
     * @return CommentContract[]
     * @throws FilterException
     * @throws ParserException
     */
    public function query($builderCallback)
    {
        $builder = DataQueryFactory::newQuery();

        /** @var DataQuery $builder */
        $tempBuilder = $builderCallback($builder);

        if ($tempBuilder !== null && $tempBuilder instanceof DataQuery) {
            $builder = $tempBuilder;
        }

        $builder->withContext(new RuntimeContext());

        return $builder->get($this->getAllSystemComments())->flattenDataset();
    }

    /**
     * Attempts to locate a thread by its identifier.
     *
     * @param string $id The thread's identifier.
     * @param bool $withTrashed Indicates if soft-deleted threads should be considered.
     * @param bool $includeComments Indicates if comments should be included with the thread.
     * @return Thread|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        $threadCandidate = $this->locateThreadCandidate($id, $withTrashed);

        if ($threadCandidate === null) {
            return null;
        }

        return $this->materializeThreadFromDatabaseRecord($threadCandidate, $includeComments);
    }

    /**
     * Attempts the provided thread.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function save(ThreadContract $thread)
    {
        return $this->updateMetaData($thread->getId(), $thread->getMetaData());
    }

    /**
     * Updates the meta data for the provided thread.
     *
     * @param string $contextId The thread's string identifier.
     * @param ThreadMetaData $metaData
     * @return bool
     */
    public function updateMetaData($contextId, ThreadMetaData $metaData)
    {
        $existingThread = $this->locateThreadCandidate($contextId, true);
        $threadContext = $this->contextResolver->findById($contextId);

        if ($existingThread === null) {
            return $this->createForContextWithMetaData($threadContext, $metaData->getDataAttributes());
        }

        if ($threadContext === null) {
            LocalErrorCodeRepository::log(ErrorLog::make(Errors::THREAD_CONTEXT_NOT_FOUND, $contextId));

            return false;
        }

        // Clear the deleted_at column if this thread is no longer trashed.
        if ($metaData->getIsTrashed() == false) {
            $existingThread->deleted_at = null;
        }

        $existingThread->meta_data = json_encode($metaData->getDataAttributes());

        $didSave = $existingThread->save();

        if ($didSave === false) {
            LocalErrorCodeRepository::log(ErrorLog::make(Errors::THREAD_META_DATA_COULD_NOT_BE_SAVED, $contextId));
        }

        return $didSave;
    }

    /**
     * Attempts to create a new thread for the provided context.
     *
     * @param ThreadContextContract $context The thread's context.
     * @param array $metaAttributes The meta data attributes.
     * @return bool
     */
    private function createForContextWithMetaData(ThreadContextContract $context, $metaAttributes)
    {
        if ($context === null) {
            return false;
        }

        $virtualPath = $this->determineVirtualPathById($context->getId());

        $threadMetaData = new DatabaseThread();
        $threadMetaData->meta_data = json_encode($metaAttributes);
        $threadMetaData->virtual_path = $virtualPath;
        $threadMetaData->context_id = $context->getId();

        if (RuntimeStateGuard::threadLocks()->isLocked() === false) {
            $lock = RuntimeStateGuard::threadLocks()->lock();

            $this->threadPipeline->creating($context, null);

            RuntimeStateGuard::threadLocks()->releaseLock($lock);
        }

        $didSave = $threadMetaData->save();

        if ($didSave === true) {
            $this->threadPipeline->created($context, null);
        }

        return $didSave;
    }

    /**
     * Generates an internal path for the given thread identifier.
     *
     * @param string $id The thread's identifier.
     * @return string
     */
    public function determineVirtualPathById($id)
    {
        return Paths::makeNew()->normalize($this->storagePath . Paths::SYM_FORWARD_SEPARATOR . $id);
    }

    /**
     * Attempts to permanently delete the provided thread instance.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function delete(ThreadContract $thread)
    {
        $wasDeleted = $this->deleteById($thread->getContextId());

        if ($wasDeleted) {
            $thread->setIsUsable(false);
        }

        return $wasDeleted;
    }

    /**
     * Attempts to remove a thread based on its identifier.
     *
     * @param string $id The thread's identifier.
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->removeById($id);
    }

    /**
     * Attempts to permanently remove a thread by its identifier.
     *
     * @param string $id The thread's identifier.
     * @return bool
     * @throws Exception
     */
    public function removeById($id)
    {
        $thread = $this->locateThreadCandidate($id, true);

        if ($thread === null) {
            return false;
        }

        $removalEventArgs = new ThreadRemovalEventArgs();
        $removalEventArgs->threadId = $id;

        if (RuntimeStateGuard::threadLocks()->isLocked() === false) {
            $lock = RuntimeStateGuard::threadLocks()->lock();

            $lastResult = null;

            $this->threadPipeline->removing($removalEventArgs, function ($result) use (&$lastResult) {
                if ($result !== null && $result instanceof ThreadRemovalEventArgs) {
                    $lastResult = $result;
                }
            });

            if ($lastResult !== null && $lastResult instanceof ThreadRemovalEventArgs) {
                if ($lastResult->shouldKeep()) {
                    RuntimeStateGuard::threadLocks()->releaseLock($lock);
                    return $this->softDeleteById($id);
                }
            }

            RuntimeStateGuard::threadLocks()->releaseLock($lock);
        }

        $deleteResults = $thread->forceDelete();
        $didRemove = true;

        if ($deleteResults === null || $deleteResults === false) {
            $didRemove = false;
        }

        if ($didRemove === true) {
            // TODO: Remove all comments for this thread.

            $this->threadPipeline->removed($this->contextResolver->findById($id), null);
        }

        return $didRemove;
    }

    /**
     * Attempts to soft-delete a thread by its identifier.
     * s
     * @param string $id The thread's identifier.
     * @return bool
     * @throws Exception
     */
    public function softDeleteById($id)
    {
        $thread = $this->locateThreadCandidate($id, true);

        if ($thread === null) {
            return false;
        }

        $deleteResults = $thread->delete();
        $wasSoftDeleted = true;

        if ($deleteResults === null || $deleteResults === false) {
            $wasSoftDeleted = false;
        }

        if ($wasSoftDeleted) {
            $this->threadPipeline->softDeleted($this->contextResolver->findById($id), null);
        }

        return $wasSoftDeleted;
    }

    /**
     * Attempts to soft-delete the provided thread instance.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function softDelete(ThreadContract $thread)
    {
        $wasSoftDeleted = $this->softDeleteById($thread->getContextId());

        if ($wasSoftDeleted) {
            $thread->setIsTrashed(true);
        }

        return $wasSoftDeleted;
    }

    /**
     * Attempts to move comments from one thread to another thread.
     *
     * @param string $sourceThreadId The identifier of the thread to move data from.
     * @param string $targetThreadId The identifier of the thread to move data to.
     * @return bool
     */
    public function moveThread($sourceThreadId, $targetThreadId)
    {
        $sourceThread = $this->locateThreadCandidate($sourceThreadId, true);
        $targetThread = $this->locateThreadCandidate($targetThreadId, true);

        if ($sourceThread === null || $targetThread === null) {
            return false;
        }

        // TODO: Implement moveThread() method.
        // TODO: Move all comments on source thread to target thread.
        // TODO: After moving comments, force delete source thread silently.
    }

    /**
     * Attempts to restore a thread by it's identifier.
     *
     * @param string $threadId The thread's identifier.
     * @return bool
     */
    public function restoreThread($threadId)
    {
        $thread = $this->locateThreadCandidate($threadId, true);

        if ($thread === null) {
            return false;
        }

        $restoreResults = $thread->restore();
        $didRestore = true;

        if ($restoreResults === null || $restoreResults === false) {
            $didRestore = false;
        }

        return $didRestore;
    }

    /**
     * Attempts to create a new thread for the provided context.
     *
     * @param ThreadContextContract $context The thread's context.
     * @return bool
     */
    public function createForContext(ThreadContextContract $context)
    {
        return $this->createForContextWithMetaData($context, []);
    }

}
