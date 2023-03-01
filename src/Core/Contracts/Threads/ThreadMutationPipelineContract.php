<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;
use Stillat\Meerkat\Core\Threads\ThreadMovingEventArgs;
use Stillat\Meerkat\Core\Threads\ThreadRemovalEventArgs;
use Stillat\Meerkat\Core\Threads\ThreadRestoringEventArgs;

/**
 * Interface ThreadMutationPipelineContract
 *
 * Responsible for responding to thread mutation requests
 *
 * @since 2.0.0
 */
interface ThreadMutationPipelineContract extends MutationPipelineContract
{
    const MUTATION_RESOLVING = 'thread.resolving';

    const MUTATION_REMOVING = 'thread.removing';

    const MUTATION_REMOVED = 'thread.removed';

    const MUTATION_SOFT_DELETED = 'thread.softDeleted';

    const MUTATION_CREATING = 'thread.beforeCreate';

    const MUTATION_CREATED = 'thread.created';

    const MUTATION_RESTORING = 'thread.restoring';

    const MUTATION_RESTORED = 'thread.restored';

    const MUTATION_MOVING = 'thread.moving';

    const MUTATION_MOVED = 'thread.moved';

    /**
     * Broadcasts that a thread's context is resolving.
     *
     * @param  ThreadContextContract  $thread The thread being resolved.
     * @param  callable  $callback A callback that will be invoked after each pipeline stop.
     * @return mixed
     */
    public function resolving(ThreadContextContract $thread, $callback);

    /**
     * Called before the thread is removed.
     *
     * @param  ThreadRemovalEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function removing(ThreadRemovalEventArgs $eventArgs, $callback);

    /**
     * Called after the thread has been removed.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function removed(ThreadContextContract $threadContext, $callback);

    /**
     * Called after a thread has been soft-deleted.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function softDeleted(ThreadContextContract $threadContext, $callback);

    /**
     * Called before the thread is created.
     *
     * @param  ThreadContextContract  $threadContext The thread context.
     * @param  callable  $callback The callback.
     */
    public function creating(ThreadContextContract $threadContext, $callback);

    /**
     * Called after the thread is created.
     *
     * @param  callable  $callback The callback.
     */
    public function created(ThreadContextContract $threadContext, $callback);

    /**
     * Called before the thread is moved to another context.
     *
     * @param  ThreadMovingEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function moving(ThreadMovingEventArgs $eventArgs, $callback);

    /**
     * Called after the thread has been moved.
     *
     * @param  callable  $callback The callback.
     */
    public function moved(ThreadContextContract $thread, $callback);

    /**
     * Called before the thread is being restored from a soft-deleted state.
     *
     * @param  ThreadRestoringEventArgs  $eventArgs The event args.
     * @param  callable  $callback The callback.
     */
    public function restoring(ThreadRestoringEventArgs $eventArgs, $callback);

    /**
     * Called after the thread has been restored from a soft-deleted state.
     *
     * @param  ThreadContextContract  $thread The thread context.
     * @param  callable  $callback The callback.
     */
    public function restored(ThreadContextContract $thread, $callback);
}
