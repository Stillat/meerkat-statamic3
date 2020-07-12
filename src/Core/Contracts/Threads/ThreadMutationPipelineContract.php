<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Responsible for responding to thread mutation requests
 *
 * @since 2.0.0
 */
interface ThreadMutationPipelineContract extends MutationPipelineContract
{


    const MUTATION_RESOLVING = 'thread.resolving';

    /**
     * Identifies a request to remove a thread.
     */
    const MUTATION_REMOVING = 'thread.beforeRemove';
    const MUTATION_REMOVED = 'thread.removed';

    const MUTATION_SOFT_DELETED = 'thread.softDeleted';

    const MUTATION_CREATING = 'thread.beforeCreate';
    const MUTATION_CREATED = 'thread.created';

    const MUTATION_MOVING = 'thread.moving';
    const MUTATION_MOVED = 'thread.moved';


    /**
     * Broadcasts that a thread's context is resolving.
     *
     * @param ThreadContextContract $thread The thread being resolved.
     * @param callable $callback A callback that will be invoked after each pipeline stop.
     * @return mixed
     */
    public function resolving(ThreadContextContract $thread, $callback);

}
