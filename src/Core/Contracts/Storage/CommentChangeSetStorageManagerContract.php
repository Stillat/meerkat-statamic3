<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSetCollection;

/**
 * Interface CommentChangeSetStorageManagerContract
 *
 * Provides a consistent API for interacting with comment change sets.
 *
 * @package Stillat\Meerkat\Core\Contracts\Storage
 * @since 2.0.0
 */
interface CommentChangeSetStorageManagerContract
{

    /**
     * Attempts to locate the change sets for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return ChangeSetCollection
     */
    public function getChangeSetForCommentId($commentId);

    /**
     * Attempts to locate the change sets for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return ChangeSetCollection
     */
    public function getChangeSetForComment(CommentContract $comment);

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param string $commentId The comment identifier.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSetById($commentId, ChangeSet $changeSet);

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param CommentContract $comment The comment.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSet(CommentContract $comment, ChangeSet $changeSet);

    /**
     * Retrieves the revision identifiers for the provided identifier.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRevisionsById($commentId);

    /**
     * Retrieves the revision identifiers for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return string[]
     */
    public function getRevisions(CommentContract $comment);

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExistsById($commentId, $revision);

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExists(CommentContract $comment, $revision);

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSetsById($commentId);

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSets(CommentContract $comment);

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevisionById($commentId, $revision);

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevision(CommentContract $comment, $revision);

    /**
     * Gets the revision count for the provided comment identifier.
     *
     * @param string $commentId The comment's identifier.
     * @return int
     */
    public function getRevisionCountById($commentId);

    /**
     * Gets the revision count for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return int
     */
    public function getRevisionCount(CommentContract $comment);

}
