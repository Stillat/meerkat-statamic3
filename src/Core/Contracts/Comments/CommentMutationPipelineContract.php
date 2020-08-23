<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

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

    /**
     * Identifies a request to remove a comment.
     */
    const MUTATION_REMOVING = 'comments.beforeRemove';
    const MUTATION_REMOVED = 'comments.removed';

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
    const MUTATION_APPROVING = 'comments.approving';
    const MUTATION_APPROVED = 'comments.approved';
    const MUTATION_UNAPPROVING = 'comments.unapproving';
    const MUTATION_UNAPPROVED = 'comments.unapproved';

    public function removing(CommentContract $comment, $callback);
    public function removed(CommentContract $comment, $callback);

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
