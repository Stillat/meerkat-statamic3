<?php

namespace Stillat\Meerkat\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
use Stillat\Meerkat\Core\Threads\ThreadMovingEventArgs;
use Stillat\Meerkat\Core\Threads\ThreadRemovalEventArgs;
use Stillat\Meerkat\Core\Threads\ThreadRestoringEventArgs;
use Stillat\Meerkat\EventPipeline;

/**
 * Class ThreadMutationPipeline
 *
 * Implements the Meerkat Core thread mutation pipeline to broadcast thread life-cycle events.
 *
 * @since 2.0.0
 */
class ThreadMutationPipeline extends EventPipeline implements ThreadMutationPipelineContract
{
    /**
     * Broadcasts that a thread's context is resolving.
     *
     * @param  ThreadContextContract  $thread The thread being resolved.
     * @param  callable  $callback A callback that will be invoked after each pipeline stop.
     */
    public function resolving(ThreadContextContract $thread, $callback)
    {
        $pipelineArgs = [$thread];

        $this->mutate(ThreadMutationPipelineContract::MUTATION_RESOLVING, $pipelineArgs, $callback);
    }

    /**
     * Called before the thread is removed.
     *
     * @param  ThreadRemovalEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function removing(ThreadRemovalEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [$eventArgs];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_REMOVING,
            $pipelineArgs,
            $callback
        );
    }

    /**
     * Called after the thread has been removed.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function removed(ThreadContextContract $threadContext, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_REMOVED,
            $threadContext,
            $callback
        );
    }

    /**
     * Called after a thread has been soft-deleted.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function softDeleted(ThreadContextContract $threadContext, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_SOFT_DELETED,
            $threadContext,
            $callback
        );
    }

    /**
     * Called before the thread is created.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function creating(ThreadContextContract $threadContext, $callback)
    {
        $pipelineArgs = [$threadContext];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_CREATING,
            $pipelineArgs,
            $callback
        );
    }

    /**
     * Called after the thread is created.
     *
     * @param  callable  $callback The callback.
     */
    public function created(ThreadContextContract $threadContext, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_CREATED,
            $threadContext,
            $callback
        );
    }

    /**
     * Called before the thread is moved to another context.
     *
     * @param  ThreadMovingEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function moving(ThreadMovingEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [$eventArgs];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_MOVING,
            $pipelineArgs,
            $callback
        );
    }

    /**
     * Called after the thread has been moved.
     *
     * @param  callable  $callback The callback.
     */
    public function moved(ThreadContextContract $thread, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_MOVED,
            $thread,
            $callback
        );
    }

    /**
     * Called before the thread is being restored from a soft-deleted state.
     *
     * @param  ThreadRestoringEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function restoring(ThreadRestoringEventArgs $eventArgs, $callback)
    {
        $pipelineArgs = [$eventArgs];

        $this->mutate(
            ThreadMutationPipelineContract::MUTATION_RESTORING,
            $pipelineArgs,
            $callback
        );
    }

    /**
     * Called after the thread has been restored from a soft-deleted state.
     *
     * @param  ThreadContextContract  $thread The thread context.
     * @param  callable  $callback The callback.
     */
    public function restored(ThreadContextContract $thread, $callback)
    {
        $this->delayMutate(
            ThreadMutationPipelineContract::MUTATION_RESTORED,
            $thread,
            $callback
        );
    }
}
