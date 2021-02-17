<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSetCollection;

class DatabaseCommentChangeSetStorageManager implements CommentChangeSetStorageManagerContract
{

    /**
     * Attempts to locate the change sets for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return ChangeSetCollection
     */
    public function getChangeSetForCommentId($commentId)
    {
        // TODO: Implement getChangeSetForCommentId() method.
    }

    /**
     * Attempts to locate the change sets for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return ChangeSetCollection
     */
    public function getChangeSetForComment(CommentContract $comment)
    {
        // TODO: Implement getChangeSetForComment() method.
    }

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param string $commentId The comment identifier.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSetById($commentId, ChangeSet $changeSet)
    {
        // TODO: Implement addChangeSetById() method.
    }

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param CommentContract $comment The comment.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSet(CommentContract $comment, ChangeSet $changeSet)
    {
        // TODO: Implement addChangeSet() method.
    }

    /**
     * Retrieves the revision identifiers for the provided identifier.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRevisionsById($commentId)
    {
        // TODO: Implement getRevisionsById() method.
    }

    /**
     * Retrieves the revision identifiers for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return string[]
     */
    public function getRevisions(CommentContract $comment)
    {
        // TODO: Implement getRevisions() method.
    }

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExistsById($commentId, $revision)
    {
        // TODO: Implement revisionExistsById() method.
    }

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExists(CommentContract $comment, $revision)
    {
        // TODO: Implement revisionExists() method.
    }

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSetsById($commentId)
    {
        // TODO: Implement removeHistoricalChangeSetsById() method.
    }

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSets(CommentContract $comment)
    {
        // TODO: Implement removeHistoricalChangeSets() method.
    }

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevisionById($commentId, $revision)
    {
        // TODO: Implement updateToRevisionById() method.
    }

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevision(CommentContract $comment, $revision)
    {
        // TODO: Implement updateToRevision() method.
    }

    /**
     * Gets the revision count for the provided comment identifier.
     *
     * @param string $commentId The comment's identifier.
     * @return int
     */
    public function getRevisionCountById($commentId)
    {
        // TODO: Implement getRevisionCountById() method.
    }

    /**
     * Gets the revision count for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return int
     */
    public function getRevisionCount(CommentContract $comment)
    {
        // TODO: Implement getRevisionCount() method.
    }
}