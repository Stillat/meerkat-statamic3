<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Stillat\Meerkat\Core\Comments\AffectsCommentsResult;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\VariableSuccessResult;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\RuntimeStateGuard;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\AbstractCommentStorageManager;
use Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models\DatabaseComment;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

class EloquentCommentStorageManager extends AbstractCommentStorageManager implements CommentStorageManagerContract
{

    /**
     * The path where all comment threads are stored.
     *
     * @var string
     */
    protected $storagePath = '';

    /**
     * The Markdown parser implementation instance.
     *
     * @var MarkdownParserContract
     */
    private $markdownParser = null;

    public function __construct(
        Configuration $config,
        MarkdownParserContract $markdownParser,
        CommentMutationPipelineContract $commentPipeline,
        CommentFactoryContract $commentFactory,
        IdentityManagerContract $identityManager,
        CommentAuthorRetriever $authorRetriever,
        CommentChangeSetStorageManagerContract $changeSetManager)
    {
        parent::__construct($config, $commentFactory, $commentPipeline, $identityManager, $authorRetriever, $changeSetManager);

        $this->markdownParser = $markdownParser;
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

        $virtualThreadPath = $this->paths->combine([$threadId]);

        return $this->getThreadHierarchy($virtualThreadPath, $threadId, $virtualPaths, $threadComments);
    }

    private function getThreadHierarchy($virtualThreadPath, $threadId, $virtualPaths, $comments)
    {
        $storageLock = RuntimeStateGuard::storageLocks()->lock();

        $hierarchy = $this->commentStructureResolver->resolve($virtualThreadPath, $virtualPaths);

        $threadCommentArray = $comments->keyBy(function (DatabaseComment $comment) {
            return $comment->compatibility_id;
        })->toArray();

        $comments = $comments->map(function (DatabaseComment $comment) {
            return $this->databaseCommentToCommentContract($comment);
        })->keyBy(function (CommentContract $comment) {
            return $comment->getId();
        })->toArray();

        $comments = collect($comments)->map(function (CommentContract $comment) use (&$hierarchy, &$comments) {
            return $this->fillWithGraphRelationships($comment, $hierarchy, $comments);
        })->toArray();

        $hierarchy->setComments($comments);

        RuntimeStateGuard::storageLocks()->releaseLock($storageLock);

        return $hierarchy;
    }

    /**
     * @param DatabaseComment $databaseComment
     * @return CommentContract
     */
    private function databaseCommentToCommentContract($databaseComment)
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

        return $comment;
    }

    private function fillWithGraphRelationships(&$comment, $hierarchy, &$commentPrototypes)
    {
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
                $commentPrototype = tap(new Comment())->setDataAttributes($parentPrototype);

                $parentAuthor = $commentPrototype->getAuthor();

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
            $comment = $simpleHierarchy->getComment($id);

            if ($comment !== null) {
                $comment->setThreadId($threadId);
            }

            return $comment;
        }

        return null;
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

    public function softDeleteById($commentId)
    {
        dd(__METHOD__);
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

}