<?php

namespace Stillat\Meerkat\Core\Contracts;

use Stillat\Meerkat\Core\Guard\SpamService;

interface SpamGuardPipelineContract extends MutationPipelineContract
{

    const MUTATION_REGISTERING = 'guard.starting';

    public function guardStarting(SpamService $service, $callback);

}
