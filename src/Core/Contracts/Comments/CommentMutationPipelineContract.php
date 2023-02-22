<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use Stillat\Meerkat\Core\Comments\CommentRemovalEventArgs;
use Stillat\Meerkat\Core\Comments\CommentRestoringEventArgs;
use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Interface CommentMutationPipelineContract
 *
 * Responsible for responding to comment data mutation requests
 *
 * @since 2.0.0
 */
interface CommentMutationPipelineContract extends MutationPipelineContract
{
    /**
     * Identifies a request for when a Comment is manifesting.
     */
    const MUTATION_COLLECTION = 'comments.collecting';

    const MUTATION_COLLECTION_ALL = 'comments.collectingAll';

    /**
     * Identifies a request to remove a comment.
     */
    const MUTATION_REMOVING = 'comments.removing';

    const MUTATION_REMOVED = 'comments.removed';

    const MUTATION_SOFT_DELETED = 'comments.softDeleted';

    const MUTATION_RESTORING = 'comments.restoring';

    const MUTATION_RESTORED = 'comments.restored';

    const MUTATION_CREATING = 'comments.creating';

    const MUTATION_CREATED = 'comments.created';

    const MUTATION_EDITING = 'comments.editing';

    const MUTATION_EDITED = 'comments.edited';

    const MUTATION_REPLYING = 'comments.replying';

    const MUTATION_REPLIED = 'comments.replied';

    const MUTATION_MARKING_AS_SPAM = 'comments.spam.markingAsSpam';

    const MUTATION_MARKED_AS_SPAM = 'comments.spam.markedAsSpam';

    const MUTATION_MARKING_AS_HAM = 'comments.spam.markingAsHam';

    const MUTATION_MARKED_AS_HAM = 'comments.spam.markedAsHam';

    const MUTATION_SPAM_CHECKING = 'comments.spam.checking';

    const MUTATION_APPROVING = 'comments.approving';

    const MUTATION_APPROVED = 'comments.approved';

    const MUTATION_UNAPPROVING = 'comments.unapproving';

    const MUTATION_UNAPPROVED = 'comments.unapproved';

    const METHOD_COLLECTING_ALL = 'collectingAll';

    const METHOD_COLLECTING = 'collecting';

    const METHOD_CREATING = 'creating';

    const METHOD_CREATED = 'created';

    const METHOD_UPDATING = 'updating';

    const METHOD_UPDATED = 'updated';

    const METHOD_REPLYING = 'replying';

    const METHOD_REPLIED = 'replied';

    const METHOD_MARKING_AS_SPAM = 'markingAsSpam';

    const METHOD_MARKED_AS_SPAM = 'markedAsSpam';

    const METHOD_MARKING_AS_HAM = 'markingAsHam';

    const METHOD_MARKED_AS_HAM = 'markedAsHam';

    const METHOD_APPROVING = 'approving';

    const METHOD_APPROVED = 'approved';

    const METHOD_UNAPPROVING = 'unapproving';

    const METHOD_UNAPPROVED = 'unapproved';

    const METHOD_CHECKING_FOR_SPAM = 'checkingForSpam';

    /**
     * Called when an individual comment is being constructed from storage.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function collecting(CommentContract $comment, $callback);

    /**
     * Called when the provided comments are being constructed from storage.
     *
     * @param  CommentContract[]  $comments The comments.
     * @param  callable  $callback An optional callback.
     */
    public function collectingAll($comments, $callback);

    /**
     * Called before a comment is removed.
     *
     * @param  CommentRemovalEventArgs  $eventArgs The event arguments.
     * @param  callable  $callback An optional callback.
     */
    public function removing(CommentRemovalEventArgs $eventArgs, $callback);

    /**
     * Called after a comment has been removed.
     *
     * This method may not have access to request values or the comment instance.
     * The comment may have been permanently removed, and existence checks
     * should be performed before taking any action against an instance.
     *
     * @param  string  $commentId The comment identifier.
     * @param  callable  $callback An optional callback.
     */
    public function removed($commentId, $callback);

    /**
     * Called after a comment has been soft-deleted.
     *
     * This method may not have access to request values or the comment instance.
     * The comment may have been permanently removed **, and existence checks
     * should be performed before taking any action against an instance.
     *
     * ** Although this is called after soft-deletes, other developer code may
     *     have permanently removed the comment instance from storage.
     *
     * @param  string  $commentId The comment identifier.
     * @param  callable  $callback An optional callback.
     */
    public function softDeleted($commentId, $callback);

    /**
     * Executes the callback with the provided arguments when a checking-for-spam request was initiated.
     *
     * @param  array  $args The arguments.
     * @param  callable  $callback The callback.
     */
    public function checkingForSpam($args, $callback);

    /**
     * Called before a comment is being restored from a soft-deleted state.
     *
     * @param  CommentRestoringEventArgs  $eventArgs The event arguments.
     * @param  callable  $callback An optional callback.
     */
    public function restoring(CommentRestoringEventArgs $eventArgs, $callback);

    /**
     * Called after a comment has been restored from a soft-deleted state.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function restored(CommentContract $comment, $callback);

    /**
     * Called before the provided comment is fully created and saved.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function creating(CommentContract $comment, $callback);

    /**
     * Called after a comment has been created.
     *
     * This method may not have access to request values, and it should
     * be assumed that only the comment is available to the plugin.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function created(CommentContract $comment, $callback);

    /**
     * Called when a comment is being updated, but before it is saved.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function updating(CommentContract $comment, $callback);

    /**
     * Called after a comment has been updated.
     *
     * This method may not have access to request values, and it should
     * be assumed that only the comment is available to the plugin.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function updated(CommentContract $comment, $callback);

    /**
     * Called before a reply is saved to disk.
     *
     * @param  CommentContract  $comment The comment.
     * @param  callable  $callback An optional callback.
     */
    public function replying(CommentContract $comment, $callback);

    /**
     * Called after a comment reply has been saved.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function replied(CommentContract $comment, $callback);

    /**
     * Called before a comment has been marked as not spam.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function markingAsSpam(CommentContract $comment, $callback);

    /**
     * Called after a comment has been marked as spam.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function markedAsSpam(CommentContract $comment, $callback);

    /**
     * Called before a comment has been marked as not spam.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function markingAsHam(CommentContract $comment, $callback);

    /**
     * Called after a comment has been marked as not spam.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function markedAsHam(CommentContract $comment, $callback);

    /**
     * Called before a comment is approved.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function approving(CommentContract $comment, $callback);

    /**
     * Called after a comment has been approved.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function approved(CommentContract $comment, $callback);

    /**
     * Called before a comment has been un-approved.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function unapproving(CommentContract $comment, $callback);

    /**
     * Called after a comment has been un-approved.
     *
     * @param  CommentContract  $comment The comment instance.
     * @param  callable  $callback An optional callback.
     */
    public function unapproved(CommentContract $comment, $callback);
}
