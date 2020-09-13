<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use DateTime;
use InvalidArgumentException;
use Stillat\Meerkat\Core\Comments\CleanableCommentAttributes;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\CommentRemovalEventArgs;
use Stillat\Meerkat\Core\Comments\CommentRestoringEventArgs;
use Stillat\Meerkat\Core\Comments\DynamicCollectedProperties;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Comments\TransientCommentAttributes;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Data\Mutations\AttributeDiff;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Validators\CommentValidator;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\ConcurrentResourceAccessViolationException;
use Stillat\Meerkat\Core\Exceptions\MutationException;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\InternalAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\PrototypeAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\PrototypeAttributeValidator;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\TruthyAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Indexing\ShadowIndex;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Storage\Validators\PathPrivilegeValidator;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Support\TypeConversions;
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
class LocalCommentStorageManager implements CommentStorageManagerContract
{

    const PATH_REPLIES_DIRECTORY = 'replies';
    const KEY_HEADERS = 'headers';
    const KEY_RAW_HEADERS = 'raw_headers';
    const KEY_CONTENT = 'content';
    const KEY_NEEDS_MIGRATION = 'needs_content_migration';

    const KEY_PATH = 'path';
    const KEY_ID = 'id';

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
     * The Meerkat configuration instance.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The path where all comment threads are stored.
     *
     * @var string
     */
    protected $storagePath = '';

    /**
     * @var Paths|null
     */
    protected $paths = null;

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
     * A cache of thread structures.
     *
     * @var array
     */
    private $threadStructureCache = [];

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
     * A list of internal attributes.
     *
     * @var array
     */
    private $internalElements = [];

    /**
     * The comment structure resolver instance.
     *
     * @var StructureResolverInterface
     */
    private $commentStructureResolver = null;

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
     * The comment index instance.
     *
     * @var ShadowIndex
     */
    private $commentShadowIndex = null;

    /**
     * The author retriever instance.
     *
     * @var CommentAuthorRetriever
     */
    private $authorRetriever = null;

    /**
     * The comment factory implementation instance.
     *
     * @var CommentFactoryContract|null
     */
    private $commentFactory = null;

    /**
     * The CommentMutationPipelineContract implementation instance.
     *
     * @var CommentMutationPipelineContract
     */
    private $commentPipeline = null;

    /**
     * The IdentityManagerContract implementation instance.
     *
     * @var IdentityManagerContract
     */
    private $identityManager = null;

    /**
     * The CommentChangeSetStorageManagerContract implementation instance.
     *
     * @var CommentChangeSetStorageManagerContract
     */
    private $changeSetManager = null;

    public function __construct(
        Configuration $config,
        YAMLParserContract $yamlParser,
        MarkdownParserContract $markdownParser,
        CommentAuthorRetriever $authorRetriever,
        CommentFactoryContract $commentFactory,
        CommentMutationPipelineContract $commentPipeline,
        IdentityManagerContract $identityManager,
        CommentChangeSetStorageManagerContract $changeSetManager)
    {
        $this->commentShadowIndex = new ShadowIndex($config);
        $this->commentStructureResolver = new LocalCommentStructureResolver();
        $this->authorRetriever = $authorRetriever;
        $this->commentPipeline = $commentPipeline;
        $this->identityManager = $identityManager;
        $this->changeSetManager = $changeSetManager;
        $this->config = $config;
        $this->paths = new Paths($this->config);

        // Quick alias for less typing.
        $this->storagePath = PathUtilities::normalize($this->config->storageDirectory);
        $this->commentFactory = $commentFactory;

        $this->prototypeElements = PrototypeAttributes::getPrototypeAttributes();
        $this->internalElements = InternalAttributes::getInternalAttributes();
        $this->truthyPrototypeElements = TruthyAttributes::getTruthyAttributes();

        $this->commentShadowIndex->setIsCommentProtoTypeIndexEnabled(false);
        $this->commentShadowIndex->setIsThreadIndexEnabled(false);

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
     * Gets all comments for the requested thread.
     *
     * @param string $threadId The identifier of the thread.
     * @return ThreadHierarchy
     */
    public function getCommentsForThreadId($threadId)
    {
        if ($this->canUseDirectory === false) {
            return new ThreadHierarchy();
        }

        if (array_key_exists($threadId, $this->threadStructureCache)) {
            return $this->threadStructureCache[$threadId];
        }

        if ($this->commentShadowIndex->hasProtoTypeIndex($threadId)) {
            return $this->commentShadowIndex->getProtoTypeIndex($threadId);
        }

        $threadPath = $this->paths->combine([$this->storagePath, $threadId]);

        $commentPaths = [];

        if ($this->commentShadowIndex->hasIndex($threadId) === false) {
            $threadFilter = $this->paths->combine([$threadPath, '*comment.md']);
            $commentPaths = $this->paths->getFilesRecursively($threadFilter);

            $this->commentShadowIndex->buildIndex($threadId, $commentPaths);
        } else {
            $commentPaths = $this->commentShadowIndex->getThreadIndex($threadId);
        }

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

            // Start: Comment Implementation Specifics (not contract).
            $comment->setStorageManager($this);
            $comment->setAuthorRetriever($this->authorRetriever);
            // End:   Comment Implementation Specifics

            $comment->setDataAttributes($commentPrototype[LocalCommentStorageManager::KEY_HEADERS]);
            $comment->setRawAttributes($commentPrototype[LocalCommentStorageManager::KEY_RAW_HEADERS]);
            $comment->setRawContent($commentPrototype[LocalCommentStorageManager::KEY_CONTENT]);
            $comment->setYamlParser($this->yamlParser);
            $comment->setMarkdownParser($this->markdownParser);

            if ($commentPrototype[LocalCommentStorageManager::KEY_NEEDS_MIGRATION]) {
                $comment->setDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, true);
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
            $commentDate->setTimestamp($commentId);

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

        $this->commentShadowIndex->buildProtoTypeIndex($threadId, $commentPrototypes);

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
        $handle = fopen($path, 'r');
        $headerDelimiterObserved = 0;
        $headers = [];
        $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = false;

        $rawHeaders = [];
        $collectHeaders = true;
        $content = '';
        $contentLine = -1;
        $alreadyFoundContent = false;

        if ($handle) {

            while (($line = fgets($handle)) !== false) {
                $trimLine = trim($line);
                $doProcessHeaders = true;

                if ($trimLine === '---') {
                    $headerDelimiterObserved += 1;
                    $doProcessHeaders = false;
                }

                if ($headerDelimiterObserved >= 2) {
                    $collectHeaders = false;
                    $doProcessHeaders = false;
                }

                if ($doProcessHeaders) {
                    if ($collectHeaders) {
                        $rawHeaders[] = $line;
                    }

                    $protoParts = explode(': ', $trimLine, 2);

                    if (is_array($protoParts) == true && count($protoParts) == 2) {
                        if ($protoParts[0] == 'comment') {
                            if (mb_strlen($protoParts[1]) > $this->config->hardCommentLengthCap) {
                                $content = mb_substr($protoParts[1], 0, $this->config->hardCommentLengthCap);
                                $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                            } else {
                                $content = $protoParts[1];
                            }

                            $alreadyFoundContent = true;
                        }

                        if (in_array($protoParts[0], $this->prototypeElements)) {
                            $headers[$protoParts[0]] = $this->cleanAttributeValue($protoParts[1]);

                            if (in_array($protoParts[0], $this->truthyPrototypeElements)) {
                                $headers[$protoParts[0]] = TypeConversions::getBooleanValue($protoParts[1]);
                            }
                        }
                    }
                }

                if ($doProcessHeaders == false && $collectHeaders == false && $alreadyFoundContent == false) {
                    $contentLine += 1;

                    if ($contentLine > 0) {
                        $content .= $line;

                        if (mb_strlen($content) > $this->config->hardCommentLengthCap) {
                            $content = mb_substr($content, 0, $this->config->hardCommentLengthCap);
                            $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                            break;
                        }
                    }
                }
            }

            fclose($handle);
        }

        return [
            LocalCommentStorageManager::KEY_HEADERS => $headers,
            LocalCommentStorageManager::KEY_RAW_HEADERS => $rawHeaders,
            LocalCommentStorageManager::KEY_CONTENT => $content,
            LocalCommentStorageManager::KEY_NEEDS_MIGRATION => $alreadyFoundContent
        ];
    }

    /**
     * Cleans an attribute value to make it consistent and usable.
     *
     * @param string $attributeValue The value to clean.
     * @return string
     */
    private function cleanAttributeValue($attributeValue)
    {
        $attributeValue = ltrim($attributeValue, '"\'');
        $attributeValue = rtrim($attributeValue, '"\'');

        return $attributeValue;
    }

    /**
     * Executes the collecting hook on the comment.
     *
     * @param CommentContract[] $comment The comment to run the hook on.
     */
    private function runCollectionHookOnComment($comment)
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
    private function runMutablePipeline($originalId, $comment, $mutation)
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
    private function mergeFromPipelineResults($currentPipelineResult, $result)
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
    private function reassignFromPipeline($originalId, $comment, $pipelineResult)
    {
        if (CommentValidator::check($pipelineResult)) {
            $pipelineResult->setDataAttribute(CommentContract::KEY_ID, $originalId);

            return $pipelineResult;
        }

        return $comment;
    }

    /**
     * @param CommentContract[] $comments The comments to mutate.
     * @return array|null
     * @throws MutationException
     */
    private function runCollectionHookOnAllComments($comments)
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
     * Attempts to retrieve a storage path from a comment's identifier.
     *
     * @param string $commentId The comment's string identifier.
     * @return string|null
     */
    public function getPathById($commentId)
    {
        $threadFilter = $this->paths->combine([$this->storagePath, '*' . $commentId . '*']);
        $commentPath = $this->paths->searchForFile($threadFilter, $this->paths->combine([$commentId, 'comment.md']), 'comment.md');

        if (is_string($commentPath)) {
            return $commentPath;
        }

        return null;
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
     * Returns access to the internal Paths instance.
     *
     * @return Paths|null
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Attempts to save the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     * @throws InvalidArgumentException
     * @throws ConcurrentResourceAccessViolationException
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
            $this->commentPipeline->created($comment, false);

            if ($comment->hasDataAttribute(CommentContract::KEY_SPAM)) {
                if ($comment->isSpam()) {
                    $this->commentPipeline->markedAsSpam($comment, null);
                } else {
                    $this->commentPipeline->markedAsHam($comment, null);
                }
            }

            if ($comment->hasDataAttribute(CommentContract::KEY_PUBLISHED)) {
                if ($comment->published()) {
                    $this->commentPipeline->approved($comment, null);
                } else {
                    $this->commentPipeline->unapproved($comment, null);
                }
            }

            if ($comment->isReply()) {
                $this->commentPipeline->replied($comment, null);
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
            $this->commentPipeline->updated($comment, false);

            if ($comment->hasDataAttribute(CommentContract::KEY_SPAM)) {
                if ($comment->isSpam()) {
                    $this->commentPipeline->markedAsSpam($comment, null);
                } else {
                    $this->commentPipeline->markedAsHam($comment, null);
                }
            }

            if ($comment->hasDataAttribute(CommentContract::KEY_PUBLISHED)) {
                if ($comment->published()) {
                    $this->commentPipeline->approved($comment, null);
                } else {
                    $this->commentPipeline->unapproved($comment, null);
                }
            }

            if ($this->config->trackChanges === true && $finalChangeSet !== null) {
                $this->changeSetManager->addChangeSet($comment, $finalChangeSet);
            }
        }

        return $didCommentSave;
    }

    /**
     * Retrieves a list of all changes made to the comment.
     *
     * @param CommentContract $comment The comment to check.
     * @return ChangeSet
     */
    public function getMutationChangeSet(CommentContract $comment)
    {
        $persistedComment = $this->findById($comment->getId());

        $persistedStorable = $this->cleanCommentStorableData($persistedComment->getStorableAttributes());
        $persistedStorable[LocalCommentChangeSetStorageManager::KEY_SPECIAL_CONTENT] = $persistedComment->getRawContent();

        $currentStorable = $this->cleanCommentStorableData($comment->getStorableAttributes());
        $currentStorable[LocalCommentChangeSetStorageManager::KEY_SPECIAL_CONTENT] = $comment->getRawContent();

        $changeSet = AttributeDiff::analyze($persistedStorable, $currentStorable);

        $changeSet->setIdentity($this->identityManager->getIdentityContext());

        return $changeSet;
    }

    /**
     * Attempts to locate a comment by its identifier.
     *
     * @param string $id The comment's string identifier.
     * @return CommentContract|null
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
                $commentPaths = [$path];

                $simpleHierarchy = $this->getThreadHierarchy($threadPath, $threadId, $commentPaths);

                if ($simpleHierarchy->hasComment($id)) {
                    return $simpleHierarchy->getComment($id);
                }
            }
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
     * Cleans the storable comment data.
     *
     * @param array $data The data to clean.
     * @return array
     */
    private function cleanCommentStorableData($data)
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
     * Runs a conditional mutation on the comment.
     *
     * @param string $originalId The original string identifier.
     * @param CommentContract $comment The comment to run mutations against.
     * @param bool $valueCheck The condition's value.
     * @param string $trueMutation The mutation to run if the check value is true.
     * @param string $falseMutation The mutation to run if the check value is false.
     * @return CommentContract
     */
    private function runConditionalMutablePipeline($originalId, $comment, $valueCheck, $trueMutation, $falseMutation)
    {
        if ($valueCheck) {
            return $this->runMutablePipeline($originalId, $comment, $trueMutation);
        }

        return $this->runMutablePipeline($originalId, $comment, $falseMutation);
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
            mkdir($directoryName, Paths::DIRECTORY_PERMISSIONS, true);
        }

        $result = file_put_contents($storagePath, $contentToSave);

        if ($result === false) {
            return false;
        }

        return true;
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
            self::PATH_REPLIES_DIRECTORY,
            $childId,
            CommentContract::COMMENT_FILENAME
        ]);
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
     * Generates an exclusion list with the provided details.
     *
     * @param string $commentId The comment identifier to exclude.
     * @return array
     */
    private function getExclusionList($commentId)
    {
        return [
            CommentContract::COMMENT_FILENAME,
            self::PATH_REPLIES_DIRECTORY,
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
    private function cleanRelatedListing($commentId, $listing)
    {
        $exclude = [
            CommentContract::COMMENT_FILENAME,
            self::PATH_REPLIES_DIRECTORY,
            $commentId
        ];

        return array_values(array_unique(array_filter($listing, function ($part) use (&$exclude) {
            return in_array($part, $exclude) === false;
        })));
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
     * Attempts to update the comment's spam status.
     *
     * @param CommentContract $comment The comment to update.
     * @param bool $isSpam Whether or not the comment is spam.
     * @return bool
     */
    public function setSpamStatus(CommentContract $comment, $isSpam)
    {
        return $this->setSpamStatusById($comment->getId(), $isSpam);
    }

    /**
     * Attempts to update the comment's spam status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isSpam Whether or not the comment is spam.
     * @return bool
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
     * Attempts to mark the comment as spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsSpam(CommentContract $comment)
    {
        return $this->setIsSpamById($comment->getId());
    }

    /**
     * Attempts to mark the comment as spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsSpamById($commentId)
    {
        return $this->setSpamStatusById($commentId, true);
    }

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsHam(CommentContract $comment)
    {
        return $this->setIsHamById($comment->getId());
    }

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsHamById($commentId)
    {
        return $this->setSpamStatusById($commentId, false);
    }

    /**
     * Attempts to update the comment's published/approved status.
     *
     * @param CommentContract $comment The comment to update.
     * @param bool $isApproved Whether the comment is "published".
     * @return bool
     */
    public function setApprovedStatus(CommentContract $comment, $isApproved)
    {
        return $this->setApprovedStatusById($comment->getId(), $isApproved);
    }

    /**
     * Attempts to update the comment's published/approved status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isApproved Whether the comment is "published".
     * @return bool
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
     * Attempts to mark the comment as approved/published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsApproved(CommentContract $comment)
    {
        return $this->setIsNotApprovedById($comment->getId());
    }

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsNotApprovedById($commentId)
    {
        return $this->setApprovedStatusById($commentId, false);
    }

    /**
     * Attempts to mark the comment as approved/published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsApprovedById($commentId)
    {
        return $this->setApprovedStatusById($commentId, true);
    }

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsNotApproved(CommentContract $comment)
    {
        return $this->setIsNotApprovedById($comment->getId());
    }

    /**
     * Attempts to remove the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function removeById($commentId)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return false;
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
                    return $this->softDeleteById($commentId);
                }
            }

            RuntimeStateGuard::mutationLocks()->releaseLock($lock);
        }

        $storageDirectory = dirname($comment->getVirtualPath());

        Paths::recursivelyRemoveDirectory($storageDirectory);

        $this->commentPipeline->removed($commentId, null);

        return true;
    }

    /**
     * Attempts to soft delete the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
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
     * Attempts to restore a soft-deleted comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     * @throws ConcurrentResourceAccessViolationException
     */
    public function restoreById($commentId)
    {
        $comment = $this->findById($commentId);

        if ($comment === null) {
            return false;
        }


        if ($comment->isDeleted() === false) {
            return false;
        }

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
                    return false;
                }
            }

            RuntimeStateGuard::mutationLocks()->releaseLock($lock);
        }

        $comment->setDataAttribute(CommentContract::KEY_IS_DELETED, false);
        $wasUpdated = $this->update($comment);

        if ($wasUpdated === true) {
            $this->commentPipeline->restored($comment, null);
        }

        return $wasUpdated;
    }

}
