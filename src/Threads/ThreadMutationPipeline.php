<?php

namespace Stillat\Meerkat\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
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

}
