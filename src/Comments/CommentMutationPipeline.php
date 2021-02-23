<?php

namespace Stillat\Meerkat\Comments;

use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry;
use Stillat\Meerkat\Core\Comments\CommentRemovalEventArgs;
use Stillat\Meerkat\Core\Comments\CommentRestoringEventArgs;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\EventPipeline;

/**
 * Class CommentMutationPipeline
 *
 * Runs Meerkat Core interactions within a Laravel environment.
 *
 * For methods that are called before a final action is taken place, only the
 * supplied arguments to the called handlers should be assumed to be
 * available at the time of invocation. All other data-values must
 * be requested by interacting with the storage systems and API.
 *
 * @package Stillat\Meerkat\Comments
 * @since 2.0.0
 */
class CommentMutationPipeline extends EventPipeline implements CommentMutationPipelineContract
{

    /**
     * A list of all handled Statamic entry identifiers for this request.
     *
     * @var array
     */
    protected $handledStatamicEntries = [];

    /**
     * Attempts to fire Statamic's EntrySaved event for the provided thread context.
     * 
     * @param string $threadId The entry identifier.
     */
    private function fireStatamicEntrySavedEvent($threadId)
    {
        if ($threadId !== null && in_array($threadId, $this->handledStatamicEntries) === false) {
            $entry = Entry::find($threadId);

            if ($entry !== null) {
                $this->handledStatamicEntries[] = $threadId;
                EntrySaved::dispatch($entry);
            }
        }
    }

    /**
     * Called when an individual comment is being constructed from storage.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function collecting(CommentContract $comment, $callback)
    {
        if ($comment->getDataAttribute(CommentContract::INTERNAL_HAS_COLLECTED) === true) {
            return;
        }

        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_COLLECTION, $pipelineArgs, $callback);
    }

    /**
     * Called when the provided comments are being constructed from storage.
     *
     * @param CommentContract[] $comments The comments.
     * @param callable $callback An optional callback.
     */
    public function collectingAll($comments, $callback)
    {
        $pipelineArgs = [
            $comments
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_COLLECTION_ALL, $pipelineArgs, $callback);
    }

    /**
     * Called before the provided comment is fully created and saved.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function creating(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_CREATING, $pipelineArgs, $callback);
    }

    /**
     * Called when a comment is being updated, but before it is saved.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function updating(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_EDITING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been created.
     *
     * This method may not have access to request values, and it should
     * be assumed that only the comment is available to the plugin.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function created(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_CREATED, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been updated.
     *
     * This method may not have access to request values, and it should
     * be assumed that only the comment is available to the plugin.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function updated(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_EDITED, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment is removed.
     *
     * @param CommentRemovalEventArgs $eventArgs The event arguments.
     * @param callable $callback An optional callback.
     */
    public function removing(CommentRemovalEventArgs $eventArgs, $callback)
    {
        if ($eventArgs !== null && count($eventArgs->contexts) > 0) {
            foreach ($eventArgs->contexts as $context) {
                $this->fireStatamicEntrySavedEvent($context);
            }
        }

        $pipelineArgs = [
            $eventArgs
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_REMOVING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been removed.
     *
     * This method may not have access to request values or the comment instance.
     * The comment may have been permanently removed, and existence checks
     * should be performed before taking any action against an instance.
     *
     * @param string $commentId The comment identifier.
     * @param callable $callback An optional callback.
     */
    public function removed($commentId, $callback)
    {
        $pipelineArgs = [
            $commentId
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_REMOVED, $pipelineArgs, $callback);
    }

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
     * @param string $commentId The comment identifier.
     * @param callable $callback An optional callback.
     */
    public function softDeleted($commentId, $callback)
    {
        $pipelineArgs = [
            $commentId
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_SOFT_DELETED, $pipelineArgs, $callback);
    }

    /**
     * Called before a reply is saved to disk.
     *
     * @param CommentContract $comment The comment.
     * @param callable $callback An optional callback.
     */
    public function replying(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_REPLYING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment reply has been saved.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function replied(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_REPLIED, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment is marked as spam.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function markingAsSpam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_MARKING_AS_SPAM, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been marked as spam.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function markedAsSpam(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_MARKED_AS_SPAM, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment has been marked as not spam.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function markingAsHam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_MARKING_AS_HAM, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been marked as not spam.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function markedAsHam(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_MARKED_AS_HAM, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment is approved.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function approving(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_APPROVING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been approved.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function approved(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_APPROVED, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment has been un-approved.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function unapproving(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_UNAPPROVING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been un-approved.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function unapproved(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_UNAPPROVED, $pipelineArgs, $callback);
    }

    /**
     * Called before a comment is being restored from a soft-deleted state.
     *
     * @param CommentRestoringEventArgs $eventArgs The event arguments.
     * @param callable $callback An optional callback.
     */
    public function restoring(CommentRestoringEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [
            $eventArgs
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_RESTORING, $pipelineArgs, $callback);
    }

    /**
     * Called after a comment has been restored from a soft-deleted state.
     *
     * @param CommentContract $comment The comment instance.
     * @param callable $callback An optional callback.
     */
    public function restored(CommentContract $comment, $callback)
    {
        if ($comment !== null) {
            $this->fireStatamicEntrySavedEvent($comment->getThreadId());
        }

        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_RESTORED, $pipelineArgs, $callback);
    }

    /**
     * Executes the callback with the provided arguments when a checking-for-spam request was initiated.
     *
     * @param array $args The arguments.
     * @param callable $callback The callback.
     */
    public function checkingForSpam($args, $callback)
    {
        $this->delayExecute($args, $callback);
    }

}
