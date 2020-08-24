<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\EventPipeline;

class GuardPipeline extends EventPipeline implements SpamGuardPipelineContract
{

    public function guardStarting(SpamService $service, $callback)
    {
        $pipelineArgs = [
            $service
        ];

        $this->mutate(SpamGuardPipelineContract::MUTATION_REGISTERING, $pipelineArgs, $callback);
    }
}
