<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use DateTime;
use Exception;
use InvalidArgumentException;
use Stillat\Meerkat\Core\Comments\AffectsCommentsResult;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\CommentRemovalEventArgs;
use Stillat\Meerkat\Core\Comments\CommentRestoringEventArgs;
use Stillat\Meerkat\Core\Comments\VariableSuccessResult;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\PrototypeParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\ConcurrentResourceAccessViolationException;
use Stillat\Meerkat\Core\Exceptions\MutationException;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Parsing\CommentPrototypeParser;
use Stillat\Meerkat\Core\Parsing\FullCommentPrototypeParser;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\AbstractCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\PrototypeAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\PrototypeAttributeValidator;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\TruthyAttributes;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Storage\Validators\PathPrivilegeValidator;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;
use Stillat\Meerkat\Core\ValidationResult;

/**
 * Class LocalCommentStorageManager
 *
 * Manages the interactions between Meerkat Comments and a local file system.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalCommentStorageManager extends AbstractCommentStorageManager implements CommentStorageManagerContract
{

    const PATH_REPLIES_DIRECTORY = 'replies';
    const PATH_COMMENT_FILE = 'comment.md';

    const KEY_HEADERS = 'headers';
    const KEY_RAW_HEADERS = 'raw_headers';
    const KEY_CONTENT = 'content';
    const KEY_NEEDS_MIGRATION = 'needs_content_migration';

    const KEY_PATH = 'path';
    const KEY_ID = 'id';

    /**
     * Indicates if the configured storage directory was validated.
     *
     * @var bool
     */
    private $directoryValidated = false;

    /**
     * Indicates if the configured storage directory is usable.
     *
     * @var bool
     */
    private $canUseDirectory = false;

    /**
     * A collection of storage directory validation results.
     *
     * @var ValidationResult
     */
    private $validationResults;

    /**
     * A list of internal attributes to scan when building comment prototypes.
     *
     * @var array
     */
    private $prototypeElements = [];

    /**
     * A list of internal "truth" attributes.
     * @var array
     */
    private $truthyPrototypeElements = [];

    /**
     * The YAML parser implementation instance.
     *
     * @var YAMLParserContract
     */
    private $yamlParser = null;

    /**
     * The Markdown parser implementation instance.
     *
     * @var MarkdownParserContract
     */
    private $markdownParser = null;

    /**
     * The PrototypeParserContract implementation instance.
     *
     * @var PrototypeParserContract
     */
    private $commentParser = null;

    public function __construct(
        Configuration $config,
        YAMLParserContract $yamlParser,
        MarkdownParserContract $markdownParser,
        CommentAuthorRetriever $authorRetriever,
        IdentityManagerContract $identityManager,
        CommentFactoryContract $commentFactory,
        CommentMutationPipelineContract $commentPipeline,
        CommentChangeSetStorageManagerContract $changeSetManager)
    {
        parent::__construct($config, $commentFactory, $commentPipeline, $identityManager, $authorRetriever, $changeSetManager);

        if ($config->useSlimCommentPrototypeParser === false) {
            $this->commentParser = new FullCommentPrototypeParser($yamlParser);
        } else {
            $this->commentParser = new CommentPrototypeParser();
        }

        $this->paths = new Paths($this->config);

        // Quick alias for less typing.
        $this->storagePath = PathUtilities::normalize($this->config->storageDirectory);

        $this->prototypeElements = PrototypeAttributes::getPrototypeAttributes();

        $this->truthyPrototypeElements = TruthyAttributes::getTruthyAttributes();

        $this->commentParser->setConfig($this->config);
        $this->commentParser->setPrototypeElements($this->prototypeElements);
        $this->commentParser->setTruthyElements($this->truthyPrototypeElements);

        $this->yamlParser = $yamlParser;
        $this->markdownParser = $markdownParser;

        $this->validationResults = new ValidationResult();
        $this->validate();
    }

    public function validate()
    {
        if ($this->directoryValidated) {
            return $this->validationResults;
        }

        $results = PathPrivilegeValidator::validatePathPermissions(
            $this->storagePath,
            Errors::DRIVER_LOCAL_INSUFFICIENT_PRIVILEGES
        );

        $this->validationResults = $results[PathPrivilegeValidator::RESULT_VALIDATION_RESULTS];
        $this->canUseDirectory = $results[PathPrivilegeValidator::RESULT_CAN_USE_DIRECTORY];

        $this->validationResults->updateValidity();
        $this->directoryValidated = true;

        return $this->validationResults;
    }

    /**
     * Gets all comments for the requested thread.
     *
     * @param string $threadId The identifier of the thread.
     * @return ThreadHierarchy
     * @throws MutationException
     */
    public function getCommentsForThreadId($threadId)
    {
        if ($this->canUseDirectory === false) {
            return new ThreadHierarchy();
        }

        if (array_key_exists($threadId, $this->threadStructureCache)) {
            return $this->threadStructureCache[$threadId];
        }

        $threadPath = $this->paths->combine([$this->storagePath, $threadId]);

        $threadFilter = $this->paths->combine([$threadPath, '*' . LocalCommentStorageManager::PATH_COMMENT_FILE]);
        $commentPaths = $this->paths->getFilesRecursively($threadFilter);

        $hierarchy = $this->getThreadHierarchy($threadPath, $threadId, $commentPaths);

        $this->threadStructureCache[$threadId] = $hierarchy;

        return $hierarchy;
    }

    /**
     * Builds a thread comment hierarchy for the provided details.
     *
     * @param string $threadPath The thread path.
     * @param string $threadId The thread's string identifier.
     * @param string[] $commentPaths The comment paths.
     * @return ThreadHierarchy
     * @throws MutationException
     */
    private function getThreadHierarchy($threadPath, $threadId, $commentPaths)
    {
        $commentPrototypes = [];
        $hierarchy = $this->commentStructureResolver->resolve($threadPath, $commentPaths);
        $storageLock = RuntimeStateGuard::storageLocks()->lock();

        for ($i = 0; $i < count($commentPaths); $i += 1) {
            // First, let's get the "prototype" form of this comment.
            $commentInternalPath = $this->paths->normalize($commentPaths[$i]);
            $commentPrototype = $this->getCommentPrototype($commentInternalPath);

            if (count($commentPrototype[LocalCommentStorageManager::KEY_HEADERS]) == 0) {
                continue;
            }

            if (array_key_exists(
                    CommentContract::KEY_ID,
                    $commentPrototype[LocalCommentStorageManager::KEY_HEADERS]) === false
            ) {
                continue;
            }

            $commentId = $commentPrototype[LocalCommentStorageManager::KEY_HEADERS][CommentContract::KEY_ID];
            $commentId = ltrim($commentId, '"\'');
            $commentId = rtrim($commentId, '"\'');

            $commentPrototype[LocalCommentStorageManager::KEY_HEADERS][CommentContract::INTERNAL_PATH] = $commentInternalPath;

            $comment = new Comment();

            $comment->setIsNew(false);
            $comment->setThreadId($threadId);
            // Start: Comment Implementation Specifics (not contract).
            $comment->setStorageManager($this);
            $comment->setAuthorRetriever($this->authorRetriever);
            // End:   Comment Implementation Specifics

            $dataAttributes = $commentPrototype[LocalCommentStorageManager::KEY_HEADERS];

            $comment->setDataAttributes($dataAttributes);
            $comment->setRawAttributes($commentPrototype[LocalCommentStorageManager::KEY_RAW_HEADERS]);
            $comment->setRawContent($commentPrototype[LocalCommentStorageManager::KEY_CONTENT]);
            $comment->setYamlParser($this->yamlParser);
            $comment->setMarkdownParser($this->markdownParser);

            $hasAuthorEmail = false;
            $hasAuthorName = false;

            if (array_key_exists(AuthorContract::KEY_EMAIL_ADDRESS, $dataAttributes)) {
                $authorEmailCandidate = $dataAttributes[AuthorContract::KEY_EMAIL_ADDRESS];

                if ($authorEmailCandidate !== null && mb_strlen(trim($authorEmailCandidate)) > 0) {
                    $hasAuthorEmail = true;
                }
            }

            if (array_key_exists(AuthorContract::KEY_NAME, $dataAttributes)) {
                $authorNameCandidate = $dataAttributes[AuthorContract::KEY_NAME];

                if ($authorNameCandidate !== null && mb_strlen(trim($authorNameCandidate)) > 0) {
                    $hasAuthorName = true;
                }
            }

            $comment->setDataAttribute(CommentContract::INTERNAL_AUTHOR_HAS_NAME, $hasAuthorName);
            $comment->setDataAttribute(CommentContract::INTERNAL_AUTHOR_HAS_EMAIL, $hasAuthorEmail);

            if ($commentPrototype[LocalCommentStorageManager::KEY_NEEDS_MIGRATION]) {
                $comment->setDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, true);
            }

            if ($this->config->useSlimCommentPrototypeParser === false) {
                $comment->flagRuntimeAttributesResolved();
            }

            $commentPrototypes[$commentId] = $comment;
        }

        $dateFormatToUse = $this->config->getFormattingConfiguration()->commentDateFormat;

        /**
         * @var string $commentId
         * @var CommentContract $comment
         */
        foreach ($commentPrototypes as $commentId => $comment) {
            $hasAncestor = $hierarchy->hasAncestor($commentId);
            $directChildren = $hierarchy->getDirectDescendents($commentId);
            $allDescendents = $hierarchy->getAllDescendents($commentId);
            $children = [];

            $isParent = count($directChildren) > 0;

            $commentDate = new DateTime();
            $commentDate->setTimestamp(intval($commentId));

            $commentDateFormatted = $commentDate->format($dateFormatToUse);

            $comment->setDataAttribute(CommentContract::KEY_COMMENT_DATE, $commentDate);
            $comment->setDataAttribute(CommentContract::KEY_COMMENT_DATE_FORMATTED, $commentDateFormatted);
            $comment->setDataAttribute(CommentContract::KEY_IS_ROOT, !$hasAncestor);
            $comment->setDataAttribute(CommentContract::KEY_IS_PARENT, $isParent);
            $comment->setDataAttribute(CommentContract::KEY_DESCENDENTS, $allDescendents);
            $comment->setDataAttribute(CommentContract::INTERNAL_CONTEXT_ID, $threadId);

            if (count($allDescendents) == 0) {
                $comment->setDataAttribute(CommentContract::INTERNAL_ABSOLUTE_ROOT, $commentId);
            } else {
                $comment->setDataAttribute(CommentContract::INTERNAL_ABSOLUTE_ROOT, $allDescendents[0]);
            }

            $comment->setDataAttribute(
                CommentContract::KEY_DEPTH,
                $hierarchy->getDepth($commentId)
            );

            $comment->setDataAttribute(
                CommentContract::KEY_ANCESTORS,
                $hierarchy->getAllAncestors($commentId)
            );

            if ($isParent) {
                foreach ($directChildren as $child) {
                    if (array_key_exists($child, $commentPrototypes)) {
                        $children[] = $commentPrototypes[$child];
                    }
                }

                $comment->setDataAttribute(CommentContract::KEY_HAS_REPLIES, true);
            } else {
                $comment->setDataAttribute(CommentContract::KEY_HAS_REPLIES, false);
            }

            if ($hasAncestor) {
                $commentParent = $hierarchy->getParent($commentId);

                if (array_key_exists($commentParent, $commentPrototypes)) {
                    /** @var CommentContract $parentPrototype */
                    $parentPrototype = $commentPrototypes[$commentParent];
                    $parentAuthor = $parentPrototype->getAuthor();

                    if ($parentAuthor !== null) {
                        $comment->setParentAuthor($parentAuthor);
                    }

                    $comment->setDataAttribute(CommentContract::KEY_PARENT, $parentPrototype);
                }
            }

            $comment->setDataAttribute(CommentContract::KEY_CHILDREN, $children);
            $comment->setDataAttribute(CommentContract::KEY_IS_REPLY, $hasAncestor);
            $comment->setReplies($children);

            $comment->setDataAttribute(
                CommentContract::INTERNAL_HISTORY_REVISION_COUNT,
                $this->changeSetManager->getRevisionCount($comment)
            );

            // Executes the comment collecting pipeline events. This is an incredibly powerful
            // tool for third-party developers, but we need to keep an eye on performance.
            $this->runCollectionHookOnComment($comment);
        }

        $this->runCollectionHookOnAllComments($commentPrototypes);

        $hierarchy->setComments($commentPrototypes);

        RuntimeStateGuard::storageLocks()->releaseLock($storageLock);

        return $hierarchy;
    }

    /**
     * Retrieves only the core meta-data for the comment.
     *
     * Supplemental data and content are ignored during this phase.
     *
     * @param string $path The full path to the comment data.
     * @return array
     */
    private function getCommentPrototype($path)
    {
        return $this->commentParser->getCommentPrototype($path);
    }

    /**
     * Attempts to save the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     * @throws InvalidArgumentException
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function save(CommentContract $comment)
    {
        RuntimeStateGuard::storageLocks()->checkConcurrentAccess();

        if ($comment === null) {
            return false;
        }

        if ($comment->getIsNew() === false) {
            return $this->update($comment);
        }

        PrototypeAttributeValidator::validateAttributes($comment->getDataAttributes());

        $originalId = $comment->getId();

        $comment = $this->runMutablePipeline($originalId, $comment, CommentMutationPipelineContract::MUTATION_CREATING);

        // Spam/ham pipeline.
        if ($comment->hasDataAttribute(CommentContract::KEY_SPAM)) {
            $comment = $this->runConditionalMutablePipeline(
                $originalId,
                $comment,
                $comment->isSpam(),
                CommentMutationPipelineContract::METHOD_MARKING_AS_SPAM,
                CommentMutationPipelineContract::METHOD_MARKING_AS_HAM
            );
        }

        // Approving/un-approving pipeline.
        if ($comment->hasDataAttribute(CommentContract::KEY_PUBLISHED)) {
            $comment = $this->runConditionalMutablePipeline(
                $originalId,
                $comment,
                $comment->published(),
                CommentMutationPipelineContract::METHOD_APPROVING,
                CommentMutationPipelineContract::METHOD_UNAPPROVING
            );
        }

        $didCommentSave = $this->persistComment($comment);

        if ($didCommentSave === true) {
            $this->commentStructureResolver->clearThreadCache($comment->getThreadId());
            // Reload the comment to supply the full comment details.
            $savedComment = $this->findById($comment->getId());

            $this->commentPipeline->created($savedComment, null);

            if ($comment->hasDataAttribute(CommentContract::KEY_SPAM)) {
                if ($comment->isSpam()) {
                    $this->commentPipeline->markedAsSpam($savedComment, null);
                } else {
                    $this->commentPipeline->markedAsHam($savedComment, null);
                }
            }

            if ($comment->hasDataAttribute(CommentContract::KEY_PUBLISHED)) {
                if ($comment->published()) {
                    $this->commentPipeline->approved($savedComment, null);
                } else {
                    $this->commentPipeline->unapproved($savedComment, null);
                }
            }

            if ($comment->isReply()) {
                $this->commentPipeline->replied($savedComment, null);
            }

        }

        return $didCommentSave;
    }

    /**
     * Attempts to update the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function update(CommentContract $comment)
    {
        RuntimeStateGuard::storageLocks()->checkConcurrentAccess();

        if ($comment === null) {
            return false;
        }

        if ($comment->getIsNew() === true) {
            return $this->save($comment);
        }

        PrototypeAttributeValidator::validateAttributes($comment->getDataAttributes());

        $changeSet = $this->getMutationChangeSet($comment);

        $originalId = $comment->getId();

        $comment = $this->runMutablePipeline($originalId, $comment, CommentMutationPipelineContract::METHOD_UPDATING);


        // Marking as spam/ham.
        if ($changeSet->wasAttributeMutated(CommentContract::KEY_SPAM)) {
            $comment = $this->runConditionalMutablePipeline(
                $originalId,
                $comment,
                $comment->isSpam(),
                CommentMutationPipelineContract::METHOD_MARKING_AS_SPAM,
                CommentMutationPipelineContract::METHOD_MARKING_AS_HAM
            );
        }

        // Approving pipeline.
        if ($changeSet->wasAttributeMutated(CommentContract::KEY_PUBLISHED)) {
            $comment = $this->runConditionalMutablePipeline(
                $originalId,
                $comment,
                $comment->published(),
                CommentMutationPipelineContract::METHOD_APPROVING,
                CommentMutationPipelineContract::METHOD_UNAPPROVING
            );
        }

        // Get the final change set before saving changes.
        $finalChangeSet = null;

        if ($this->config->trackChanges) {
            $finalChangeSet = $this->getMutationChangeSet($comment);
        }

        $didCommentSave = $this->persistComment($comment);

        if ($didCommentSave === true) {
            $this->commentStructureResolver->clearThreadCache($comment->getThreadId());
            // Reload the comment to supply the full comment details.
            $updatedComment = $this->findById($comment->getId());

            $this->commentPipeline->updated($updatedComment, null);

            if ($comment->hasDataAttribute(CommentContract::KEY_SPAM)) {
                if ($comment->isSpam()) {
                    $this->commentPipeline->markedAsSpam($updatedComment, null);
                } else {
                    $this->commentPipeline->markedAsHam($updatedComment, null);
                }
            }

            if ($comment->hasDataAttribute(CommentContract::KEY_PUBLISHED)) {
                if ($comment->published()) {
                    $this->commentPipeline->approved($updatedComment, null);
                } else {
                    $this->commentPipeline->unapproved($updatedComment, null);
                }
            }

            if ($this->config->trackChanges === true && $finalChangeSet !== null) {
                $this->changeSetManager->addChangeSet($comment, $finalChangeSet);
            }
        }

        return $didCommentSave;
    }

    /**
     * Attempts to persist the comment data to disk.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     */
    private function persistComment(CommentContract $comment)
    {
        $storableAttributes = $comment->getStorableAttributes();

        $storableAttributes = $this->cleanCommentStorableData($storableAttributes);

        $storagePath = $comment->getVirtualPath();
        $contentToSave = $this->yamlParser->toYaml($storableAttributes, $comment->getRawContent());
        $directoryName = dirname($storagePath);

        if (!file_exists($directoryName)) {
            mkdir($directoryName, Paths::$directoryPermissions, true);
        }

        $result = file_put_contents($storagePath, $contentToSave);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to locate a comment by its identifier.
     *
     * @param string $id The comment's string identifier.
     * @return CommentContract|null
     * @throws MutationException
     */
    public function findById($id)
    {
        $path = $this->getPathById($id);

        if ($path !== null) {
            $threadDetails = $this->getThreadDetailsFromPath($path);

            if ($threadDetails !== null) {
                $threadId = $threadDetails[self::KEY_ID];
                $threadPath = $threadDetails[self::KEY_PATH];
                // Convert the path to an array.
                $commentPaths = $this->inferRelationshipsFromPath($id, $path);

                $simpleHierarchy = $this->getThreadHierarchy($threadPath, $threadId, $commentPaths);

                if ($simpleHierarchy->hasComment($id)) {
                    $comment = $simpleHierarchy->getComment($id);

                    if ($comment !== null) {
                        $comment->setThreadId($threadId);
                    }

                    return $comment;
                }
            }
        }

        return null;
    }

    /**
     * Attempts to retrieve a storage path from a comment's identifier.
     *
     * @param string $commentId The comment's string identifier.
     * @return string|null
     */
    public function getPathById($commentId)
    {
        $threadFilter = $this->paths->combine([$this->storagePath, '*' . $commentId . '*']);
        $commentPath = $this->paths->searchForFile($threadFilter, $this->paths->combine(
            [$commentId, LocalCommentStorageManager::PATH_COMMENT_FILE]),
            LocalCommentStorageManager::PATH_COMMENT_FILE);

        if (is_string($commentPath)) {
            return $commentPath;
        }

        return null;
    }

    /**
     * Gets thread information from a comment's physical path.
     *
     * @param string $path The comment path to analyze.
     * @return array|null
     */
    private function getThreadDetailsFromPath($path)
    {
        $relativePath = $this->paths->makeRelative($path);

        if (Str::startsWith($relativePath, Paths::SYM_FORWARD_SEPARATOR)) {
            $relativePath = mb_substr($relativePath, 1);
        }

        $parts = explode(Paths::SYM_FORWARD_SEPARATOR, $relativePath);

        if (is_array($parts) && count($parts) > 0) {
            $threadId = $parts[0];
            $threadPath = $this->paths->combine([$this->storagePath, $threadId]);

            return [
                self::KEY_ID => $threadId,
                self::KEY_PATH => $threadPath
            ];
        }

        return null;
    }

    /**
     * Attempts to locate the comment's parent and child comment identifiers and paths.
     *
     * The return value will be an array.
     *   - The key will be the comment identifier.
     *   - The value will be the directory of the comment.
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getRelatedCommentsPaths($commentId)
    {
        if (array_key_exists($commentId, self::$relatedPathCache) === false) {
            $rootLen = mb_strlen($this->storagePath) + 1;
            $relatedRootPath = dirname($this->getPathById($commentId));
            $subRoot = mb_substr($relatedRootPath, $rootLen);

            $rootParts = explode(Paths::SYM_FORWARD_SEPARATOR, $subRoot);

            if (count($rootParts) === 0) {
                return [];
            }

            $exclude = $this->getExclusionList($commentId);

            $threadId = $rootParts[0];

            $rootLen = mb_strlen($this->paths->combine([$this->storagePath, $threadId])) + 1;

            $commentPath = $this->paths->combine([$relatedRootPath, '*']);
            $paths = $this->paths->getFilesRecursively($commentPath);
            $subParts = [];

            $pathMapping = [];

            foreach ($paths as $path) {
                $subPath = mb_substr($path, $rootLen);
                $parts = array_merge($subParts, explode(Paths::SYM_FORWARD_SEPARATOR, $subPath));

                if (count($parts) === 0) {
                    continue;
                }

                $parts = array_slice($parts, 1);

                $mappedPath = dirname($path);
                $mappingParts = explode(Paths::SYM_FORWARD_SEPARATOR, $mappedPath);

                if (count($mappingParts) > 0) {
                    $startProcessingPaths = false;

                    for ($i = 0; $i < count($mappingParts); $i++) {
                        if ($mappingParts[$i] === $threadId) {
                            $startProcessingPaths = true;
                            continue;
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
                };
                $subParts = array_merge($subParts, $parts);
                $subParts = array_unique($subParts);
            }

            $pathMappingToReturn = [];
            $cleanedSubparts = $this->cleanRelatedListing($commentId, $subParts);

            foreach ($cleanedSubparts as $subCommentId) {
                if (array_key_exists($subCommentId, $pathMapping)) {
                    $pathMappingToReturn[$subCommentId] = $pathMapping[$subCommentId];
                }
            }

            self::$relatedPathCache[$commentId] = $pathMappingToReturn;
        }

        return self::$relatedPathCache[$commentId];
    }

    /**
     * Attempts to update the comments' spam status.
     *
     * @param array $commentIds The comment identifiers.
     * @param bool $isSpam Whether or not the comments are spam.
     * @return VariableSuccessResult
     */
    public function setSpamStatusForIds($commentIds, $isSpam)
    {
        $result = new VariableSuccessResult();

        foreach ($commentIds as $commentId) {
            if ($commentId === null) {
                continue;
            }

            try {
                if ($this->setSpamStatusById($commentId, $isSpam) === true) {
                    $result->succeeded[$commentId] = true;
                    $result->comments[] = $commentId;
                } else {
                    $result->failed[$commentId] = false;
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $result->failed[$commentId] = $e;
            }
        }

        return $result->updateState();
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
     * Attempts to update the published/approved status for the provided comment identifiers.
     *
     * @param array $commentIds The comment identifiers to update.
     * @param bool $isApproved Whether the comments are "published".
     * @return VariableSuccessResult
     */
    public function setApprovedStatusForIds($commentIds, $isApproved)
    {
        $result = new VariableSuccessResult();

        foreach ($commentIds as $commentId) {
            if ($commentId === null) {
                continue;
            }

            try {
                if ($this->setApprovedStatusById($commentId, $isApproved) === true) {
                    $result->succeeded[$commentId] = true;
                    $result->comments[] = $commentId;
                } else {
                    $result->failed[$commentId] = false;
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $result->failed[$commentId] = $e;
            }
        }

        return $result->updateState();
    }

    /**
     * Attempts to update the comment's published/approved status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isApproved Whether the comment is "published".
     * @return bool
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function setApprovedStatusById($commentId, $isApproved)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return false;
        }

        $comment->setDataAttribute(CommentContract::KEY_PUBLISHED, $isApproved);

        return $this->update($comment);
    }

    /**
     * Attempts to remove all of the provided comments.
     *
     * @param array $commentIds The comments to remove.
     * @return VariableSuccessResult
     */
    public function removeAll($commentIds)
    {
        $result = new VariableSuccessResult();

        foreach ($commentIds as $commentId) {
            try {
                $removeResult = $this->removeById($commentId);

                if ($removeResult->success === true) {
                    $result->succeeded[$commentId] = $removeResult;

                    $result->comments[] = $commentId;
                    $result->comments = array_merge($result->comments, array_map('strval', array_keys($removeResult->comments)));
                } else {
                    if (!in_array($commentId, $result->comments)) {
                        $result->failed[$commentId] = $removeResult;
                    }
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $result->failed[$commentId] = $e;
            }
        }

        return $result->updateState();
    }

    /**
     * Attempts to remove the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return AffectsCommentsResult
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function removeById($commentId)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return AffectsCommentsResult::failed();
        }

        $descendents = $this->getDescendentsPaths($commentId);

        $commentRemovalEventArgs = new CommentRemovalEventArgs();
        $commentRemovalEventArgs->comment = $comment;

        if (count($descendents) > 0) {
            $commentRemovalEventArgs->effectedComments = array_keys($descendents);
            $commentRemovalEventArgs->willRemoveOthers = true;
        }

        if (RuntimeStateGuard::mutationLocks()->isLocked() === false) {
            $lastResult = null;

            $lock = RuntimeStateGuard::mutationLocks()->lock();

            $this->commentPipeline->removing($commentRemovalEventArgs, function ($result) use (&$lastResult) {
                if ($result !== null && $result instanceof CommentRemovalEventArgs) {
                    $lastResult = $result;
                }
            });

            if ($lastResult !== null && $lastResult instanceof CommentRemovalEventArgs) {
                if ($lastResult->shouldKeep()) {
                    RuntimeStateGuard::mutationLocks()->releaseLock($lock);
                    $didSoftDelete = $this->softDeleteById($commentId);

                    return AffectsCommentsResult::conditionalWithComments($didSoftDelete, $descendents);
                }
            }

            RuntimeStateGuard::mutationLocks()->releaseLock($lock);
        }

        $storageDirectory = dirname($comment->getVirtualPath());

        Paths::recursivelyRemoveDirectory($storageDirectory);

        $this->commentPipeline->removed($commentId, null);

        $descendents[] = $commentId;

        return AffectsCommentsResult::successWithComments($descendents);
    }

    /**
     * Attempts to locate the comment's child comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendentsPaths($commentId)
    {
        if (array_key_exists($commentId, self::$descendentPathCache) === false) {
            $rootPath = dirname($this->getPathById($commentId));
            $rootLen = mb_strlen($rootPath) + 1;
            $commentPath = $this->paths->combine([$rootPath, '*']);
            $paths = $this->paths->getFilesRecursively($commentPath);
            $subParts = [];

            $exclude = $this->getExclusionList($commentId);

            $pathMapping = [];

            foreach ($paths as $path) {
                $subPath = mb_substr($path, $rootLen);
                $subParts = array_merge($subParts, explode(Paths::SYM_FORWARD_SEPARATOR, $subPath));


                if (Str::startsWith($path, $rootPath)) {
                    $mappedPath = dirname($path);
                    $mappingParts = explode(Paths::SYM_FORWARD_SEPARATOR, $mappedPath);

                    if (count($mappingParts) > 0) {
                        $startProcessingPaths = false;

                        for ($i = 0; $i < count($mappingParts); $i++) {
                            if ($mappingParts[$i] === $commentId) {
                                $startProcessingPaths = true;
                                continue;
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
                }
            }

            $pathMappingToReturn = [];
            $cleanedSubparts = $this->cleanRelatedListing($commentId, $subParts);

            foreach ($cleanedSubparts as $subCommentId) {
                if (array_key_exists($subCommentId, $pathMapping)) {
                    $pathMappingToReturn[$subCommentId] = $pathMapping[$subCommentId];
                }
            }

            self::$descendentPathCache[$commentId] = $pathMappingToReturn;
        }

        return self::$descendentPathCache[$commentId];
    }

    /**
     * Attempts to soft delete the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function softDeleteById($commentId)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return false;
        }

        $comment->setDataAttribute(CommentContract::KEY_IS_DELETED, true);
        $wasUpdated = $this->update($comment);

        if ($wasUpdated === true) {
            $this->commentPipeline->softDeleted($commentId, null);
        }

        return $wasUpdated;
    }

    /**
     * Attempts to soft delete the provided comments.
     *
     * @param array $commentIds The comments to soft delete
     * @return VariableSuccessResult
     */
    public function softDeleteAll($commentIds)
    {
        $result = new VariableSuccessResult();

        foreach ($commentIds as $comment) {
            try {
                $deleteResult = $this->softDeleteById($comment);

                if ($deleteResult === true) {
                    $result->succeeded[$comment] = true;
                    $result->comments[] = $comment;
                } else {
                    $result->failed[$comment] = false;
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $result->failed[$comment] = $e;
            }
        }

        return $result->updateState();
    }

    /**
     * Attempts to restore the provided comments.
     *
     * @param array $commentIds The comments to restore.
     * @return VariableSuccessResult
     */
    public function restoreAll($commentIds)
    {
        $result = new VariableSuccessResult();

        foreach ($commentIds as $commentId) {
            try {
                $restoreResult = $this->restoreById($commentId);

                if ($restoreResult->success === true) {
                    $result->succeeded[$commentId] = $restoreResult;
                    $result->comments[] = $commentId;
                } else {
                    $result->failed[$commentId] = $restoreResult;
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $result->failed[$commentId] = $e;
            }
        }

        return $result->updateState();
    }

    /**
     * Attempts to restore a soft-deleted comment.
     *
     * @param string $commentId The comment's identifier.
     * @return AffectsCommentsResult
     * @throws ConcurrentResourceAccessViolationException
     * @throws MutationException
     */
    public function restoreById($commentId)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return AffectsCommentsResult::failed();
        }


        if ($comment->isDeleted() === false) {
            return AffectsCommentsResult::failed();
        }

        $descendents = $this->getDescendents($commentId);

        $commentRestoreEventArgs = new CommentRestoringEventArgs();
        $commentRestoreEventArgs->commentId = $commentId;
        $commentRestoreEventArgs->comment = $comment;

        if (RuntimeStateGuard::mutationLocks()->isLocked() === false) {
            $lock = RuntimeStateGuard::mutationLocks()->lock();

            $lastResult = null;

            $this->commentPipeline->restoring($commentRestoreEventArgs, function ($result) use (&$lastResult) {
                if ($result !== null && $result instanceof CommentRestoringEventArgs) {
                    $lastResult = $result;
                }
            });

            if ($lastResult !== null && $lastResult instanceof CommentRestoringEventArgs) {
                if ($lastResult->shouldRestore() === false) {
                    RuntimeStateGuard::mutationLocks()->releaseLock($lock);
                    return AffectsCommentsResult::failed();
                }
            }

            RuntimeStateGuard::mutationLocks()->releaseLock($lock);
        }

        $comment->setDataAttribute(CommentContract::KEY_IS_DELETED, false);
        $wasUpdated = $this->update($comment);

        if ($wasUpdated === true) {
            $this->commentPipeline->restored($comment, null);
        }

        return AffectsCommentsResult::conditionalWithComments($wasUpdated, $descendents);
    }

}
