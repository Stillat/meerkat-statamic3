<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Stillat\Meerkat\Core\Comments\AffectsCommentsResult;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\DynamicCollectedProperties;
use Stillat\Meerkat\Core\Comments\VariableSuccessResult;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Validators\CommentValidator;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Storage\CommentArrayStructureResolver;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models\DatabaseComment;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStructureResolver;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

class EloquentCommentStorageManager implements CommentStorageManagerContract
{


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
     * The comment structure resolver instance.
     *
     * @var StructureResolverInterface
     */
    private $commentStructureResolver = null;

    /**
     * The author retriever instance.
     *
     * @var CommentAuthorRetriever
     */
    private $authorRetriever = null;

    /**
     * The Markdown parser implementation instance.
     *
     * @var MarkdownParserContract
     */
    private $markdownParser = null;

    /**
     * The CommentMutationPipelineContract implementation instance.
     *
     * @var CommentMutationPipelineContract
     */
    private $commentPipeline = null;

    public function __construct(
        Configuration $config,
        MarkdownParserContract $markdownParser,
        CommentMutationPipelineContract $commentPipeline,
        CommentAuthorRetriever $authorRetriever)
    {
        $this->config = $config;
        $this->markdownParser = $markdownParser;
        $this->authorRetriever = $authorRetriever;
        $this->commentPipeline = $commentPipeline;
        $this->paths = new Paths($config);
        $this->commentStructureResolver = new LocalCommentStructureResolver();
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
     * Attempts to locate a comment by it's identifier.
     *
     * @param string $id The comment's string identifier.
     * @return CommentContract|null
     */
    public function findById($id)
    {
        /** @var DatabaseComment $databaseComment */
        $databaseComment = DatabaseComment::where('compatibility_id', $id)->withTrashed()->first();

        if ($databaseComment === null) {
            return null;
        }

        $virtualPath = $databaseComment->virtual_path;
        $virtualPaths = $this->inferRelationshipsFromPath($databaseComment->compatibility_id, $virtualPath);
        $threadComments = collect([$databaseComment]);
        $threadId = $databaseComment->thread_context_id;
        $virtualThreadPath = $this->paths->combine([$databaseComment->thread_context_id]);

        $simpleHierarchy = $this->getThreadHierarchy($virtualThreadPath, $threadId, $virtualPaths, $threadComments);

        if ($simpleHierarchy->hasComment($id)) {
            $comment =  $simpleHierarchy->getComment($id);

            if ($comment !== null) {
                $comment->setThreadId($threadId);
            }

            return $comment;
        }

        return null;
    }

    /**
     * Infers ancestor relationships from the comment path, to assist with lazy-loading.
     *
     * @param string $id The known comment identifier.
     * @param string $path The comment path.
     * @return string[]
     * @since 2.0.12
     */
    private function inferRelationshipsFromPath($id, $path)
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
     * Generates a virtual path for the provided thread and comment.
     *
     * @param string $threadId The thread identifier.
     * @param string $commentId The comment identifier.
     * @return string
     */
    public function generateVirtualPath($threadId, $commentId)
    {
        return $this->paths->combine([
            $threadId, $commentId, CommentContract::COMMENT_FILENAME
        ]);
    }

    /**
     * Constructs a comment from the prototype data.
     *
     * @param array $data The comment prototype.
     * @return CommentContract|null
     */
    public function makeFromArrayPrototype($data)
    {
        dd(__METHOD__);
        // TODO: Implement makeFromArrayPrototype() method.
    }

    /**
     * Attempts to get the storage path for the provided comment.
     *
     * @param string $commentId The comment's identifier.
     * @return string
     */
    public function getPathById($commentId)
    {
        /** @var DatabaseComment $databaseComment */
        $databaseComment = DatabaseComment::where('compatibility_id', $commentId)->withTrashed()->first();

        if ($databaseComment === null) {
            return null;
        }

        return $databaseComment->virtual_path;
    }

    /**
     * Attempts to get the reply storage path for the provided parent and child comment.
     *
     * @param string $parentId The parent comment's identifier.
     * @param string $childId The child comment's identifier.
     * @return string
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
     * @param DatabaseComment $databaseComment
     * @param ThreadHierarchy $hierarchy
     * @param DatabaseComment[] $commentPrototypes
     * @return CommentContract
     */
    private function databaseCommentToCommentContract($databaseComment, ThreadHierarchy &$hierarchy, $commentPrototypes)
    {
        $comment = new Comment();
        $dataAttributes = json_decode($databaseComment->comment_attributes, true);

        $comment->setThreadId($databaseComment->thread_context_id);

        $comment->setRawContent($databaseComment->content);
        $comment->setDataAttributes($dataAttributes);
        $comment->setRawAttributes($dataAttributes);
        $comment->setIsNew(false);

        $comment->flagRuntimeAttributesResolved();

        // Start: Comment Implementation Specifics (not contract).
        $comment->setStorageManager($this);
        $comment->setAuthorRetriever($this->authorRetriever);
        // End:   Comment Implementation Specifics

        $comment->setYamlParser(null);
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

        $threadId = $comment->getThreadId();
        $commentId = $comment->getId();

        $hasAncestor = $hierarchy->hasAncestor($commentId);
        $directChildren = $hierarchy->getDirectDescendents($commentId);
        $allDescendents = $hierarchy->getAllDescendents($commentId);
        $children = [];

        $isParent = count($directChildren) > 0;

        $commentDate = new DateTime();
        $commentDate->setTimestamp(intval($commentId));

        $commentDateFormatted = $commentDate->format($this->config->getFormattingConfiguration()->commentDateFormat);

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

        // TODO: Revision count.

        // Executes the comment collecting pipeline events. This is an incredibly powerful
        // tool for third-party developers, but we need to keep an eye on performance.
        $this->runCollectionHookOnComment($comment);

        return $comment;
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
     * Executes the collecting hook on the comment.
     *
     * @param CommentContract $comment The comment to run the hook on.
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
     * Returns only the virtual paths for all comments in the provided thread.
     *
     * @param string $threadId The thread's context identifier.
     * @return string[]
     */
    private function getVirtualPathsForThread($threadId)
    {
        return DB::table('meerkat_comments')
            ->select('virtual_path')->where('thread_context_id', $threadId)
            ->get()->pluck('virtual_path')->values()->sortByDesc(function ($path) {
                return mb_strlen($path);
            })->toArray();
    }

    /**
     * Gets all comments for the requested thread.
     *
     * @param string $threadId The identifier of the thread.
     * @return ThreadHierarchy
     */
    public function getCommentsForThreadId($threadId)
    {

        /** @var Collection $threadComments */
        $threadComments = DatabaseComment::where('thread_context_id', $threadId)->withTrashed()->get();
        $virtualPaths = $threadComments->pluck('virtual_path')->values()->sortByDesc(function ($path) {
            return mb_strlen($path);
        })->toArray();

        $virtualThreadPath =  $this->paths->combine([$threadId]);

        return $this->getThreadHierarchy($virtualThreadPath, $threadId, $virtualPaths, $threadComments);
    }

    private function getThreadHierarchy($virtualThreadPath, $threadId, $virtualPaths, $comments)
    {
        $storageLock = RuntimeStateGuard::storageLocks()->lock();

        $hierarchy = $this->commentStructureResolver->resolve($virtualThreadPath, $virtualPaths);

        $threadCommentArray = $comments->keyBy(function (DatabaseComment $comment) {
            return $comment->compatibility_id;
        })->toArray();

        $comments = $comments->map(function (DatabaseComment $comment) use (&$hierarchy, &$threadCommentArray) {
            return $this->databaseCommentToCommentContract($comment, $hierarchy, $threadCommentArray);
        })->keyBy(function (CommentContract $comment) {
            return $comment->getId();
        })->toArray();

        $hierarchy->setComments($comments);

        RuntimeStateGuard::storageLocks()->releaseLock($storageLock);

        return $hierarchy;
    }

    /**
     * Attempts to save the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
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

        $databaseComment = new DatabaseComment();
        $databaseComment->parent_compatibility_id = $comment->getParentId();
        $databaseComment->compatibility_id = $comment->getId();
        $databaseComment->thread_context_id = $comment->getThreadId();
        $databaseComment->depth = $comment->getDepth();
        $databaseComment->is_root = $comment->isRoot();
        $databaseComment->is_parent = $comment->isParent();
        $databaseComment->is_published = $comment->published();
        $databaseComment->content = $comment->getRawContent();
        $databaseComment->virtual_path = $comment->getVirtualPath();
        $databaseComment->comment_attributes = json_encode($comment->getDataAttributes());

        if ($comment->leftByAuthenticatedUser()) {
            $author = $comment->getAuthor();

            if ($author !== null && $author->getIsTransient() === false) {
                $databaseComment->statamic_user_id = $author->getId();
            }
        }

        if ($comment->hasBeenCheckedForSpam()) {
            $databaseComment->is_spam = $comment->isSpam();
        } else {
            $databaseComment->is_spam = null;
        }

        $didCommentSave = $databaseComment->save();

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
     * Retrieves a list of all changes made to the comment.
     *
     * @param CommentContract $comment The comment to check.
     * @return ChangeSet
     */
    public function getMutationChangeSet(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement getMutationChangeSet() method.
    }

    /**
     * Attempts to update the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     */
    public function update(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement update() method.
    }

    /**
     * Attempts to the update the comments' spam status.
     *
     * @param CommentContract[] $comments The comments to update.
     * @param bool $isSpam Whether or not the comments are spam.
     * @return VariableSuccessResult
     */
    public function setSpamStatusForComments($comments, $isSpam)
    {
        dd(__METHOD__);
        // TODO: Implement setSpamStatusForComments() method.
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
        dd(__METHOD__);
        // TODO: Implement setSpamStatusForIds() method.
    }

    /**
     * Attempts to mark the comments as spam.
     *
     * @param array $commentIds The comment identifiers.
     * @return VariableSuccessResult
     */
    public function setIsSpamForIds($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement setIsSpamForIds() method.
    }

    /**
     * Attempts to mark the comments as not spam.
     *
     * @param array $commentIds The comment identifiers.
     * @return VariableSuccessResult
     */
    public function setIsHamForIds($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement setIsHamForIds() method.
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
        dd(__METHOD__);
        // TODO: Implement setSpamStatus() method.
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
        dd(__METHOD__);
        // TODO: Implement setSpamStatusById() method.
    }

    /**
     * Attempts to mark the comment as spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsSpam(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement setIsSpam() method.
    }

    /**
     * Attempts to mark the comment as spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsSpamById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement setIsSpamById() method.
    }

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsHam(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement setIsHam() method.
    }

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsHamById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement setIsHamById() method.
    }

    /**
     * Attempts to update the published/approved status for the provided comments.
     *
     * @param CommentContract[] $comments The comments to update.
     * @param bool $isApproved Whether the comments are "published".
     * @return VariableSuccessResult
     */
    public function setApprovedStatusFor($comments, $isApproved)
    {
        dd(__METHOD__);
        // TODO: Implement setApprovedStatusFor() method.
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
        dd(__METHOD__);
        // TODO: Implement setApprovedStatusForIds() method.
    }

    /**
     * Attempts to mark the provided comments as approved.
     *
     * @param array $commentIds The comments to update.
     * @return VariableSuccessResult
     */
    public function setIsApprovedForIds($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement setIsApprovedForIds() method.
    }

    /**
     * Attempts to mark the provided comments as not approved.
     *
     * @param array $commentIds The comments to update.
     * @return VariableSuccessResult
     */
    public function setIsNotApprovedForIds($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement setIsNotApprovedForIds() method.
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
        dd(__METHOD__);
        // TODO: Implement setApprovedStatus() method.
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
        dd(__METHOD__);
        // TODO: Implement setApprovedStatusById() method.
    }

    /**
     * Attempts to mark the comment as approved/published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsApproved(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement setIsApproved() method.
    }

    /**
     * Attempts to mark the comment as approved/published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsApprovedById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement setIsApprovedById() method.
    }

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsNotApproved(CommentContract $comment)
    {
        dd(__METHOD__);
        // TODO: Implement setIsNotApproved() method.
    }

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsNotApprovedById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement setIsNotApprovedById() method.
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
        dd(__METHOD__);
        // TODO: Implement isChildOf() method.
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
        dd(__METHOD__);
        // TODO: Implement isParentOf() method.
    }

    /**
     * Attempts to locate the comment's child comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendents($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getDescendents() method.
    }

    /**
     * Attempts to locate the comment's child comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendentsPaths($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getDescendentsPaths() method.
    }

    /**
     * Attempts to locate the comment's parent comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestors($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getAncestors() method.
    }

    /**
     * Attempts to locate the comment's parent comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestorsPaths($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getAncestorsPaths() method.
    }

    /**
     * Attempts to locate the comment's parent and child comment identifiers.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRelatedComments($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getRelatedComments() method.
    }

    /**
     * Attempts to locate the comment's parent and child comment identifiers and paths.
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getRelatedCommentsPaths($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement getRelatedCommentsPaths() method.
    }

    /**
     * Attempts to remove the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return AffectsCommentsResult
     */
    public function removeById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement removeById() method.
    }

    /**
     * Attempts to remove all of the provided comments.
     *
     * @param array $commentIds The comments to remove.
     * @return VariableSuccessResult
     */
    public function removeAll($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement removeAll() method.
    }

    /**
     * Attempts to soft delete the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return AffectsCommentsResult
     */
    public function softDeleteById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement softDeleteById() method.
    }

    /**
     * Attempts to soft delete the provided comments.
     *
     * @param array $commentIds The comments to soft delete
     * @return VariableSuccessResult
     */
    public function softDeleteAll($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement softDeleteAll() method.
    }

    /**
     * Attempts to restore a soft-deleted comment.
     *
     * @param string $commentId The comment's identifier.
     * @return AffectsCommentsResult
     */
    public function restoreById($commentId)
    {
        dd(__METHOD__);
        // TODO: Implement restoreById() method.
    }

    /**
     * Attempts to restore the provided comments.
     *
     * @param array $commentIds The comments to restore.
     * @return VariableSuccessResult
     */
    public function restoreAll($commentIds)
    {
        dd(__METHOD__);
        // TODO: Implement restoreAll() method.
    }
}