<?php

namespace Stillat\Meerkat\Core\Contracts\Permissions;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

interface PermissionsMutationPipelineContract extends MutationPipelineContract
{

    const MUTATION_RESOLVING = 'permissions.resolving';

}