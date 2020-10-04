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
 * @package Stillat\Meerkat\Core\Contracts\Comments
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

    public function collecting(CommentContract $comment, $callback);
    public function collectingAll($comments, $callable);

    public function removing(CommentRemovalEventArgs $eventArgs, $callback);
    public function removed($commentId, $callback);
    public function softDeleted($commentId, $callback);

    public function checkingForSpam($args, $callback);

    public function restoring(CommentRestoringEventArgs $eventArgs, $callback);
    public function restored(CommentContract $comment, $callback);

    /**
     * @param CommentContract $comment
     * @param $callback
     * @return CommentContract
     */
    public function creating(CommentContract $comment, $callback);
    public function created(CommentContract $comment, $callback);

    public function updating(CommentContract $comment, $callback);
    public function updated(CommentContract $comment, $callback);

    public function replying(CommentContract $comment, $callback);
    public function replied(CommentContract $comment, $callback);

    public function markingAsSpam(CommentContract $comment, $callback);
    public function markedAsSpam(CommentContract $comment, $callback);

    public function markingAsHam(CommentContract $comment, $callback);
    public function markedAsHam(CommentContract $comment, $callback);

    public function approving(CommentContract $comment, $callback);
    public function approved(CommentContract $comment, $callback);

    public function unapproving(CommentContract $comment, $callback);
    public function unapproved(CommentContract $comment, $callback);

}
