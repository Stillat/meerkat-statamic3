<?php

namespace Stillat\Meerkat\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\EventPipeline;

/**
 * Class ThreadMutationPipeline
 *
 * Implements the Meerkat Core thread mutation pipeline to broadcast thread life-cycle events.
 *
 * @package Stillat\Meerkat\Threads
 * @since 2.0.0
 */
class ThreadMutationPipeline extends EventPipeline implements ThreadMutationPipelineContract
{


    /**
     * Broadcasts that a thread's context is resolving.
     *
     * @param ThreadContextContract $thread The thread being resolved.
     * @param callable $callback A callback that will be invoked after each pipeline stop.
     * @return mixed
     */
    public function resolving(ThreadContextContract $thread, $callback)
    {
        $pipelineArgs = [$thread];

        $this->mutate(ThreadMutationPipelineContract::MUTATION_RESOLVING, $pipelineArgs, $callback);
    }

    public function removing(ThreadContract $thread, $callback)
    {
        $pipelineArgs = [$thread];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_REMOVING,
            $pipelineArgs,
            $callback
        );
    }

    public function removed(ThreadContextContract $threadContext, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_REMOVED,
            $threadContext,
            $callback
        );
    }

    public function softDeleted(ThreadContextContract $threadContext, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_SOFT_DELETED,
            $threadContext,
            $callback
        );
    }

    public function creating(ThreadContextContract $threadContext, $callback)
    {
        $pipelineArgs = [$threadContext];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_CREATING,
            $pipelineArgs,
            $callback
        );
    }

    public function created(ThreadContract $thread, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_CREATED,
            $thread,
            $callback
        );
    }

    public function moving(ThreadContract $thread, $callback)
    {
        $pipelineArgs = [$thread];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_MOVING,
            $pipelineArgs,
            $callback
        );
    }

    public function moved(ThreadContract $thread, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_MOVED,
            $thread,
            $callback
        );
    }
}
