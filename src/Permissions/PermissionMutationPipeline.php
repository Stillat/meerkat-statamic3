<?php

namespace Stillat\Meerkat\Permissions;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsMutationPipelineContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;
use Stillat\Meerkat\EventPipeline;

/**
 * Class PermissionMutationPipeline
 *
 * Implements the permissions pipeline so that other developers may
 * hook into the Meerakt<>Statamic permission-resolving process.
 *
 * @package Stillat\Meerkat\Permissions
 * @since 2.0.0
 */
class PermissionMutationPipeline extends EventPipeline implements PermissionsMutationPipelineContract
{

    /**
     * Broadcasts that a permission set is being resolved.
     *
     * @param AuthorContract $identity The author identity being resolved.
     * @param PermissionsSet $permissionsSet The Meerkat-resolved permissions.
     * @param callable $callback A callback that will be invoked with the result of each pipeline stop.
     * @return mixed
     */
    public function resolving(AuthorContract $identity, PermissionsSet $permissionsSet, $callback)
    {
        $pipelineArgs = [
            $identity,
            $permissionsSet
        ];

        $this->mutate(PermissionsMutationPipelineContract::MUTATION_RESOLVING, $pipelineArgs, $callback);
    }

}
