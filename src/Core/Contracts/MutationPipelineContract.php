<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 *
 * Defines a consistent API for performing mutations
 *
 * Mutation pipelines allow implementing systems to inject
 * operations at various points in the Meerkat Core life
 * cycle. All mutators should always return an object
 * of the same form that was supplied to mutators.
 *
 * @since 2.0.0
 */
interface MutationPipelineContract
{

    /**
     * Broadcasts requests that implementations may mutate and return a modified object.
     *
     * @param string $request The type of mutation request to propagate.
     * @param mixed $object A reference to the object to mutate.
     * @param callable $callback A callback to be applied to each pipeline stop.
     * @return mixed
     */
    public function mutate($request, &$object, $callback);

}
