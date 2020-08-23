<?php

namespace Stillat\Meerkat\Comments;

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

        $this->mutate(CommentMutationPipelineContract::MUTATION_CREATED, $pipelineArgs, $callback);
    }

    public function updated(CommentContract $comment, $callback)
    {
        $pipelineArgs = [
            $comment
        ];

        $this->mutate(CommentMutationPipelineContract::MUTATION_EDITED, $pipelineArgs, $callback);
    }

    public function removing(CommentContract $comment, $callback)
    {
        // TODO: Implement removing() method.
    }

    public function removed(CommentContract $comment, $callback)
    {
        // TODO: Implement removed() method.
    }

    public function replying(CommentContract $comment, $callback)
    {
        // TODO: Implement replying() method.
    }

    public function replied(CommentContract $comment, $callback)
    {
        // TODO: Implement replied() method.
    }

    public function markingAsSpam(CommentContract $comment, $callback)
    {
        // TODO: Implement markingAsSpam() method.
    }

    public function markedAsSpam(CommentContract $comment, $callback)
    {
        // TODO: Implement markedAsSpam() method.
    }

    public function markingAsHam(CommentContract $comment, $callback)
    {
        // TODO: Implement markingAsHam() method.
    }

    public function markedAsHam(CommentContract $comment, $callback)
    {
        // TODO: Implement markedAsHam() method.
    }

    public function approving(CommentContract $comment, $callback)
    {
        // TODO: Implement approving() method.
    }

    public function approved(CommentContract $comment, $callback)
    {
        // TODO: Implement approved() method.
    }

    public function unapproving(CommentContract $comment, $callback)
    {
        // TODO: Implement unapproving() method.
    }

    public function unapproved(CommentContract $comment, $callback)
    {
        // TODO: Implement unapproved() method.
    }
}
