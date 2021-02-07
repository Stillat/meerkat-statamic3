<?php

namespace Stillat\Meerkat\Core\Storage\Drivers;

use Stillat\Meerkat\Core\Comments\CleanableCommentAttributes;
use Stillat\Meerkat\Core\Comments\DynamicCollectedProperties;
use Stillat\Meerkat\Core\Comments\TransientCommentAttributes;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Data\Mutations\AttributeDiff;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Validators\CommentValidator;
use Stillat\Meerkat\Core\Exceptions\ConcurrentResourceAccessViolationException;
use Stillat\Meerkat\Core\Exceptions\MutationException;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\InternalAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentChangeSetStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStructureResolver;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;

abstract class AbstractCommentStorageManager implements CommentStorageManagerContract
{

    /**
     * A run-time cache of comments and their descendent comment identifiers and paths.
     *
     * @var array
     */
    protected static $descendentPathCache = [];

    /**
     * A run-time cache of comments and their ancestor comment identifiers and paths.
     *
     * @var array
     */
    protected static $ancestorPathCache = [];

    /**
     * A run-time cache of comments and their related comment identifiers and paths.
     *
     * @var array
     */
    protected static $relatedPathCache = [];

    /**
     * A cache of thread structures.
     *
     * @var array
     */
    protected $threadStructureCache = [];

    /**
     * The path where all comment threads are stored.
     *
     * @var string
     */
    protected $storagePath = '';

    /**
     * The Meerkat configuration instance.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The CommentMutationPipelineContract implementation instance.
     *
     * @var CommentMutationPipelineContract
     */
    protected $commentPipeline = null;

    /**
     * @var Paths|null
     */
    protected $paths = null;

    /**
     * The author retriever instance.
     *
     * @var CommentAuthorRetriever
     */
    protected $authorRetriever = null;

    /**
     * The comment structure resolver instance.
     *
     * @var StructureResolverInterface
     */
    protected $commentStructureResolver = null;

    /**
     * The comment factory implementation instance.
     *
     * @var CommentFactoryContract|null
     */
    protected $commentFactory = null;

    /**
     * The IdentityManagerContract implementation instance.
     *
     * @var IdentityManagerContract
     */
    protected $identityManager = null;

    /**
     * A list of internal attributes.
     *
     * @var array
     */
    private $internalElements = [];

    /**
     * The CommentChangeSetStorageManagerContract implementation instance.
     *
     * @var CommentChangeSetStorageManagerContract
     */
    protected $changeSetManager = null;

    public function __construct(
        Configuration $config,
        CommentFactoryContract $commentFactory,
        CommentMutationPipelineContract $commentPipeline,
        IdentityManagerContract $identityManager,
        CommentAuthorRetriever $authorRetriever,
        CommentChangeSetStorageManagerContract $changeSetManager
    )
    {
        $this->commentStructureResolver = new LocalCommentStructureResolver();

        $this->config = $config;
        $this->commentPipeline = $commentPipeline;
        $this->commentFactory = $commentFactory;
        $this->identityManager = $identityManager;
        $this->authorRetriever = $authorRetriever;
        $this->changeSetManager = $changeSetManager;
        $this->internalElements = InternalAttributes::getInternalAttributes();
        $this->paths = new Paths($this->config);
    }

    /**
     * Retrieves a list of all changes made to the comment.
     *
     * @param CommentContract $comment The comment to check.
     * @return ChangeSet
     * @throws MutationException
     */
    public function getMutationChangeSet(CommentContract $comment)
    {
        $persistedComment = $this->findById($comment->getId());

        $persistedStorable = $this->cleanCommentStorableData($persistedComment->getStorableAttributes());
        $persistedStorable[LocalCommentChangeSetStorageManager::KEY_SPECIAL_CONTENT] = $persistedComment->getRawContent();

        $currentStorable = $this->cleanCommentStorableData($comment->getStorableAttributes());
        $currentStorable[LocalCommentChangeSetStorageManager::KEY_SPECIAL_CONTENT] = $comment->getRawContent();

        $changeSet = AttributeDiff::analyze($persistedStorable, $currentStorable);

        $identity = $this->identityManager->getIdentityContext();

        if ($identity !== null) {
            $changeSet->setIdentity($this->identityManager->getIdentityContext());
        }

        return $changeSet;
    }

    /**
     * Cleans the storable comment data.
     *
     * @param array $data The data to clean.
     * @return array
     */
    protected function cleanCommentStorableData($data)
    {
        foreach ($this->internalElements as $attribute) {
            if (array_key_exists($attribute, $data)) {
                unset($data[$attribute]);
            }
        }

        foreach (DynamicCollectedProperties::$generatedAttributes as $attribute) {
            if (array_key_exists($attribute, $data)) {
                unset($data[$attribute]);
            }
        }

        $data = TransientCommentAttributes::filter($data);
        $data = CleanableCommentAttributes::clean($data);

        return $data;
    }

    /**
     * Constructs a comment from the prototype data.
     *
     * @param array $data The comment prototype.
     * @return CommentContract|null
     */
    public function makeFromArrayPrototype($data)
    {
        return $this->commentFactory->makeComment($data);
    }

    /**
     * Generates a storage replies for the provided identifiers.
     *
     * @param string $parentId The parent comment's identifier.
     * @param string $childId The child comment's identifier.
     * @return string|string[]
     */
    public function getReplyPathById($parentId, $childId)
    {
        $basePath = $this->getPathById($parentId);

        if (Str::endsWith($basePath, CommentContract::COMMENT_FILENAME)) {
            $basePath = mb_substr($basePath, 0, -1 * (mb_strlen(CommentContract::COMMENT_FILENAME)));
        }

        if (Str::endsWith($basePath, Paths::SYM_FORWARD_SEPARATOR)) {
            $basePath = mb_substr($basePath, 0, -1);
        }

        return $this->paths->combine([
            $basePath,
            LocalCommentStorageManager::PATH_REPLIES_DIRECTORY,
            $childId,
            CommentContract::COMMENT_FILENAME
        ]);
    }

    /**
     * Attempts to update the comment's spam status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isSpam Whether or not the comment is spam.
     * @return bool
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function setSpamStatusById($commentId, $isSpam)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return false;
        }

        $comment->setDataAttribute(CommentContract::KEY_SPAM, $isSpam);

        return $this->update($comment);
    }

    /**
     * Generates a virtual storage path for the provided details.
     *
     * @param string $threadId The thread's identifier.
     * @param string $commentId The comment's identifier.
     * @return string
     */
    public function generateVirtualPath($threadId, $commentId)
    {
        return $this->getPaths()->combineWithStorage([
            $threadId,
            $commentId,
            CommentContract::COMMENT_FILENAME
        ]);
    }

    /**
     * Gets the virtual path manager.
     *
     * @return Paths
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param CommentContract[] $comments The comments to mutate.
     * @return array|null
     * @throws MutationException
     */
    protected function runCollectionHookOnAllComments($comments)
    {
        if ($comments === null || is_array($comments) === false || count($comments) === 0) {
            return $comments;
        }

        $originalIdMapping = [];
        $preMutationAttributeMapping = [];

        foreach ($comments as $key => $comment) {
            $originalIdMapping[$key] = $comment->getId();
            $preMutationAttributeMapping[$key] = $comment->getDataAttributeNames();
        }

        $lock = RuntimeStateGuard::mutationLocks()->lock();

        $pipelineResult = $comments;
        $handlersEncountered = 0;

        $this->commentPipeline->collectingAll($comments, function ($result) use (&$pipelineResult, &$handlersEncountered) {
            $pipelineResult = $result;
            $handlersEncountered += 1;
        });

        RuntimeStateGuard::mutationLocks()->releaseLock($lock);

        if ($handlersEncountered === 0) {
            unset($originalIdMapping);
            unset($preMutationAttributeMapping);

            return $comments;
        }

        if ($pipelineResult !== null && is_array($pipelineResult) && count($pipelineResult) === count($comments)) {
            $comments = $pipelineResult;
        }

        // Restore the original identifiers in case they were modified.
        // We will also track any dynamic properties added. We need
        // to do this for each comment instance since it possible
        // that developers have conditionally applied them.

        foreach ($comments as $key => $comment) {
            if (array_key_exists($key, $originalIdMapping) && array_key_exists($key, $preMutationAttributeMapping)) {
                $preMutationAttributes = $preMutationAttributeMapping[$key];
                $comment->setDataAttribute(CommentContract::KEY_ID, $originalIdMapping[$key]);
                $postMutationAttributes = $comment->getDataAttributeNames();

                $comment->setDataAttribute(CommentContract::INTERNAL_HAS_COLLECTED, true);

                DynamicCollectedProperties::registerDynamicProperties($preMutationAttributes, $postMutationAttributes);
            } else {
                throw new MutationException('Collection handlers must not change array keys. Missing: ' . $key);
            }
        }

        // Some clean up.
        unset($preMutationAttributeMapping);
        unset($originalIdMapping);

        return $comments;
    }

    /**
     * Executes the collecting hook on the comment.
     *
     * @param CommentContract $comment The comment to run the hook on.
     */
    protected function runCollectionHookOnComment($comment)
    {
        if ($comment === null || $comment instanceof CommentContract === false) {
            return;
        }

        $preMutationAttributes = $comment->getDataAttributeNames();

        $this->runMutablePipeline($comment->getId(),
            $comment, CommentMutationPipelineContract::METHOD_COLLECTING);

        $comment->setDataAttribute(CommentContract::INTERNAL_HAS_COLLECTED, true);
        $postCollectionAttributes = $comment->getDataAttributeNames();

        DynamicCollectedProperties::registerDynamicProperties(
            $preMutationAttributes, $postCollectionAttributes
        );
    }

    /**
     * Runs the requested mutation on the comment.
     *
     * @param string $originalId The original string identifier.
     * @param CommentContract $comment The comment to run mutations against.
     * @param string $mutation The mutation to run.
     * @return mixed
     */
    protected function runMutablePipeline($originalId, $comment, $mutation)
    {
        if (RuntimeStateGuard::mutationLocks()->isLocked()) {
            return $comment;
        }

        $lock = RuntimeStateGuard::mutationLocks()->lock();

        /** @var CommentContract|null $pipelineResult */
        $pipelineResult = null;

        if (method_exists($this->commentPipeline, $mutation)) {
            $this->commentPipeline->$mutation($comment, function ($result) use (&$pipelineResult) {
                $pipelineResult = $this->mergeFromPipelineResults($pipelineResult, $result);
            });
        }

        RuntimeStateGuard::mutationLocks()->releaseLock($lock);

        return $this->reassignFromPipeline($originalId, $comment, $pipelineResult);
    }

    /**
     * Attempts to merge a new result into an existing pipeline result.
     *
     * @param CommentContract|null $currentPipelineResult The current pipeline result.
     * @param CommentContract|null $result The result to merge.
     * @return CommentContract|null
     */
    protected function mergeFromPipelineResults($currentPipelineResult, $result)
    {
        if (CommentValidator::check($result)) {
            if ($currentPipelineResult === null) {
                $currentPipelineResult = $result;
            } else {
                $currentPipelineResult->mergeAttributes($result->getStorableAttributes());
            }
        }

        return $currentPipelineResult;
    }

    /**
     * Reassigns the result's identifier.
     * @param string $originalId The comment identifier.
     * @param CommentContract $comment The comment to reassign to.
     * @param CommentContract|null $pipelineResult The aggregate pipeline result.
     * @return CommentContract
     */
    protected function reassignFromPipeline($originalId, $comment, $pipelineResult)
    {
        if (CommentValidator::check($pipelineResult)) {
            $pipelineResult->setDataAttribute(CommentContract::KEY_ID, $originalId);

            return $pipelineResult;
        }

        return $comment;
    }

    /**
     * Runs a conditional mutation on the comment.
     *
     * @param string $originalId The original string identifier.
     * @param CommentContract $comment The comment to run mutations against.
     * @param bool $valueCheck The condition's value.
     * @param string $trueMutation The mutation to run if the check value is true.
     * @param string $falseMutation The mutation to run if the check value is false.
     * @return CommentContract
     */
    protected function runConditionalMutablePipeline($originalId, $comment, $valueCheck, $trueMutation, $falseMutation)
    {
        if ($valueCheck) {
            return $this->runMutablePipeline($originalId, $comment, $trueMutation);
        }

        return $this->runMutablePipeline($originalId, $comment, $falseMutation);
    }

    /**
     * Infers ancestor relationships from the comment path, to assist with lazy-loading.
     *
     * @param string $id The known comment identifier.
     * @param string $path The comment path.
     * @return string[]
     * @since 2.0.12
     */
    protected function inferRelationshipsFromPath($id, $path)
    {
        $resolvedPaths = [];
        $subStructurePath = $this->paths->combine([
            LocalCommentStorageManager::PATH_REPLIES_DIRECTORY,
            $id,
            LocalCommentStorageManager::PATH_COMMENT_FILE
        ]);

        if (Str::endsWith($path, $subStructurePath)) {
            $parentPath = $this->paths->combine([
                mb_substr($path, 0, mb_strlen($path) - mb_strlen($subStructurePath)),
                LocalCommentStorageManager::PATH_COMMENT_FILE
            ]);

            if (file_exists($parentPath)) {
                $resolvedPaths[] = $parentPath;
            }
        }

        $resolvedPaths[] = $path;

        return $resolvedPaths;
    }

    /**
     * Tests if the parent identifier is the direct ancestor of the provided comment.
     *
     * @param string $testParent The parent identifier to test.
     * @param string $commentId The child identifier to test.
     * @return bool
     */
    public function isParentOf($testParent, $commentId)
    {
        return $this->isChildOf($commentId, $testParent);
    }


    /**
     * Tests if the provided comment identifier is a descendent of the parent.
     *
     * @param string $commentId The child identifier to test.
     * @param string $testParent The parent identifier to test.
     * @return bool
     */
    public function isChildOf($commentId, $testParent)
    {
        $path = $this->getPathById($commentId);
        $testParentPos = mb_strpos($path, $testParent);

        if ($testParentPos === false) {
            return false;
        }

        $commentPos = mb_strpos($path, $commentId);

        if ($testParentPos < $commentPos) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to locate the comment's parent comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestors($commentId)
    {
        return array_keys($this->getAncestorsPaths($commentId));
    }

    /**
     * Attempts to locate the comment's parent comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestorsPaths($commentId)
    {
        if (array_key_exists($commentId, self::$ancestorPathCache) === false) {
            $commentPath = $this->getPathById($commentId);
            $subPath = mb_substr($commentPath, mb_strlen($this->storagePath) + 1);
            $parts = explode(Paths::SYM_FORWARD_SEPARATOR, $subPath);

            if (count($parts) === 0) {
                return [];
            }

            $threadId = array_shift($parts);

            $exclude = $this->getExclusionList($commentId);

            $pathMapping = [];
            $mappingParts = explode(Paths::SYM_FORWARD_SEPARATOR, $commentPath);

            if (count($mappingParts) > 0) {
                $startProcessingPaths = false;

                for ($i = 0; $i < count($mappingParts); $i++) {
                    if ($mappingParts[$i] === $threadId) {
                        $startProcessingPaths = true;
                        continue;
                    }

                    if ($mappingParts[$i] === $commentId) {
                        break;
                    }

                    if ($startProcessingPaths === false) {
                        continue;
                    }

                    if (in_array($mappingParts[$i], $exclude) === false) {
                        if (array_key_exists($mappingParts[$i], $pathMapping) === false) {
                            $subMappingParts = array_slice($mappingParts, 0, $i + 1);

                            $pathMapping[$mappingParts[$i]] = implode(Paths::SYM_FORWARD_SEPARATOR, $subMappingParts);
                        }
                    }
                }
            }

            $pathMappingToReturn = [];
            $cleanedSubparts = $this->cleanRelatedListing($commentId, $parts);

            foreach ($cleanedSubparts as $subCommentId) {
                if (array_key_exists($subCommentId, $pathMapping)) {
                    $pathMappingToReturn[$subCommentId] = $pathMapping[$subCommentId];
                }
            }

            self::$ancestorPathCache[$commentId] = $pathMappingToReturn;
        }

        return self::$ancestorPathCache[$commentId];
    }

    /**
     * Generates an exclusion list with the provided details.
     *
     * @param string $commentId The comment identifier to exclude.
     * @return array
     */
    protected function getExclusionList($commentId)
    {
        return [
            CommentContract::COMMENT_FILENAME,
            LocalCommentStorageManager::PATH_REPLIES_DIRECTORY,
            $commentId
        ];
    }

    /**
     * Removes structural information from the list of comment identifiers.
     *
     * @param string $commentId The comment identifier.
     * @param array $listing The list of comment identifiers.
     * @return array
     */
    protected function cleanRelatedListing($commentId, $listing)
    {
        $exclude = [
            CommentContract::COMMENT_FILENAME,
            LocalCommentStorageManager::PATH_REPLIES_DIRECTORY,
            $commentId
        ];

        return array_values(array_unique(array_filter($listing, function ($part) use (&$exclude) {
            return in_array($part, $exclude) === false;
        })));
    }

    /**
     * Attempts to locate the comment's parent and child comment identifiers.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRelatedComments($commentId)
    {
        return array_keys($this->getRelatedCommentsPaths($commentId));
    }

    /**
     * Attempts to locate the comment's child comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendents($commentId)
    {
        return array_keys($this->getDescendentsPaths($commentId));
    }

}
