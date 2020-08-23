<?php

namespace Stillat\Meerkat\Comments;

use Stillat\Meerkat\Core\Comments\CommentRemovalEventArgs;
use Stillat\Meerkat\Core\Comments\CommentRestoringEventArgs;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\EventPipeline;

class CommentMutationPipeline extends EventPipeline implements CommentMutationPipelineContract
{

    public function creating(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_CREATING, $pipelineArgs, $callback);
    }

    public function updating(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_EDITING, $pipelineArgs, $callback);
    }

    public function created(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_CREATED, $pipelineArgs, $callback);
    }

    public function updated(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_EDITED, $pipelineArgs, $callback);
    }

    public function removing(CommentRemovalEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [
            $eventArgs
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_REMOVING, $pipelineArgs, $callback);
    }

    public function removed($commentId, $callback)
    {
        $pipelineArgs = [
            $commentId
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_REMOVED, $pipelineArgs, $callback);
    }

    public function softDeleted($commentId, $callback)
    {
        $pipelineArgs = [
            $commentId
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_SOFT_DELETED, $pipelineArgs, $callback);
    }

    public function replying(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_REPLYING, $pipelineArgs, $callback);
    }

    public function replied(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_REPLIED, $pipelineArgs, $callback);
    }

    public function markingAsSpam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_MARKING_AS_SPAM, $pipelineArgs, $callback);
    }

    public function markedAsSpam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_MARKED_AS_SPAM, $pipelineArgs, $callback);
    }

    public function markingAsHam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_MARKING_AS_HAM, $pipelineArgs, $callback);
    }

    public function markedAsHam(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_MARKED_AS_HAM, $pipelineArgs, $callback);
    }

    public function approving(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_APPROVING, $pipelineArgs, $callback);
    }

    public function approved(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_APPROVED, $pipelineArgs, $callback);
    }

    public function unapproving(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_UNAPPROVING, $pipelineArgs, $callback);
    }

    public function unapproved(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_UNAPPROVED, $pipelineArgs, $callback);
    }

    public function restoring(CommentRestoringEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [
            $eventArgs
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_RESTORING, $pipelineArgs, $callback);
    }

    public function restored(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->delayMutate(CommentMutationPipelineContract::MUTATION_RESTORED, $pipelineArgs, $callback);
    }
}
