<?php

namespace Stillat\Meerkat;

use Stillat\Meerkat\Concerns\EmitsEvents;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Class EventPipeline
 *
 * Provides a consistent implementation for managing event mutation pipelines.
 *
 * @package Stillat\Meerkat
 * @since 2.0.0
 */
abstract class EventPipeline implements MutationPipelineContract
{
    use EmitsEvents, UsesConfig;

    public function delayMutate($request, $object, $callback)
    {
        $this->mutate($request, $object, $callback);

        // Disabling delayed mutations as they cause way more problems than they are worth
        // at the moment. A future update might be to have the "after" ones run in a queue.
        /* $runSync = $this->getConfig('internals.runDelayedMutationsSync', false);

        if ($runSync === false) {
            app()->terminating(function () use ($request, &$object, $callback) {
                $this->mutate($request, $object, $callback);
            });
        } else {
            $this->mutate($request, $object, $callback);
        }*/
    }

    /**
     * Broadcasts requests that implementations may mutate and return a modified object.
     *
     * @param string $request The type of mutation request to propagate.
     * @param mixed $object A reference to the object to mutate.
     * @param callable $callback A callback to be applied to each pipeline stop.
     */
    public function mutate($request, &$object, $callback)
    {
        foreach ($this->emitEvent($request, $object) as $pipelineStop) {
            if ($callback !== null && is_string($callback)) {
                $callback($pipelineStop);
            }
        }
    }

    /**
     * Attempts to execute the callback after the current request.
     *
     * @param string[] $args The arguments.
     * @param callable $callback The callback to execute later.
     */
    public function delayExecute($args, $callback)
    {
        $runSync = $this->getConfig('internals.runDelayedMutationsSync', false);

        if ($runSync === false) {
            app()->terminating(function () use ($args, $callback) {
                $callback($args);
            });
        } else {
            $callback($args);
        }
    }

}
