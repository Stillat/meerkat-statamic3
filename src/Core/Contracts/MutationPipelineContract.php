<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 * Interface MutationPipelineContract
 *
 * Defines a consistent API for performing mutations
 *
 * Mutation pipelines allow implementing systems to inject
 * operations at various points in the Meerkat Core life
 * cycle. All mutators should always return an object
 * of the same form that was supplied to mutators.
 *
 * @package Stillat\Meerkat\Core\Contracts
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

    /**
     * Broadcasts requests that implementations.
     *
     * Implementations may choose to delay the execution of these requests by using a job queue, or some other means.
     *
     * @param string $request The type of mutation request to propagate.
     * @param mixed $object A reference to the object to mutate.
     * @param callable $callback A callback to be applied to each pipeline stop.
     * @return mixed
     */
    public function delayMutate($request, $object, $callback);

}
