<?php

namespace Stillat\Meerkat\Core\Contracts\Permissions;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;
use Stillat\Meerkat\Core\Permissions\PermissionsSet;

/**
 * Interface PermissionsMutationPipelineContract
 *
 * Provides a cross-platform API for modifying the results of a permission resolution.
 *
 * @package Stillat\Meerkat\Core\Contracts\Permissions
 * @since 2.0.0
 */
interface PermissionsMutationPipelineContract extends MutationPipelineContract
{

    /**
     * The name of the pipeline indicating a permission set is being resolved.
     */
    const MUTATION_RESOLVING = 'permissions.resolving';

    /**
     * Broadcasts that a permission set is being resolved.
     *
     * @param AuthorContract $identity The author identity being resolved.
     * @param PermissionsSet $permissionsSet The Meerkat-resolved permissions.
     * @param callable $callback A callback that will be invoked with the result of each pipeline stop.
     *
     * @return mixed
     */
    public function resolving(AuthorContract $identity, PermissionsSet $permissionsSet, $callback);

}
