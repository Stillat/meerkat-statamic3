<?php

namespace Stillat\Meerkat;

use Stillat\Meerkat\Concerns\EmitsEvents;
use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Class EventPipeline
 * @package Stillat\Meerkat
 * @since 2.0.0
 */
abstract class EventPipeline implements MutationPipelineContract
{
    use EmitsEvents;

    /**
     * Broadcasts requests that implementations may mutate and return a modified object.
     *
     * @param string $request The type of mutation request to propagate.
     * @param mixed $object A reference to the object to mutate.
     * @param callable $callback A callback to be applied to each pipeline stop.
     * @return mixed
     */
    public function mutate($request, &$object, $callback)
    {
        foreach ($this->emitEvent($request, $object) as $pipelineStop) {
            $callback($pipelineStop);
        }
    }

}
