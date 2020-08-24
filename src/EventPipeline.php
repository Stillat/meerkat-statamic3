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
        $runSync = $this->getConfig('internals.delayMutationSync', false);

        if ($runSync === false) {
            app()->terminating(function () use ($request, &$object, $callback) {
                $this->mutate($request, $object, $callback);
            });
        } else {
            $this->mutate($request, $object, $callback);
        }
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
            if ($callback !== null) {
                $callback($pipelineStop);
            }
        }
    }

}
