<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\EventPipeline;

/**
 * Class GuardPipeline
 *
 * Provides interactions between Meerkat Core and Statamic/Laravel event systems.
 *
 * @package Stillat\Meerkat\Guard
 * @since 2.0.0
 */
class GuardPipeline extends EventPipeline implements SpamGuardPipelineContract
{

    /**
     * Called when the Spam Service is starting.
     *
     * @param SpamService $service The spam service.
     * @param callable $callback An optional callback.
     */
    public function guardStarting(SpamService $service, $callback)
    {
        $pipelineArgs = [
            $service
        ];

        $this->mutate(SpamGuardPipelineContract::MUTATION_REGISTERING, $pipelineArgs, $callback);
    }

}
