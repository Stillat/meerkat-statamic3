<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Interface SanitationMutationPipelineContract
 *
 * Provides a consistent API for communicating with Meerkat Sanitizer Addons.
 *
 * @since 2.0.0
 */
interface SanitationMutationPipelineContract extends MutationPipelineContract
{
    const MUTATION_REGISTERING = 'sanitizer.registering';

    /**
     * Provides an opportunity for Meerkat addons to interact with the sanitation manager.
     *
     * @param  SanitationManagerContract  $manager The comment sanitation manager.
     * @param  callable  $callback The callback to execute when registering sanitizers.
     * @return void
     */
    public function registeringSanitizers(SanitationManagerContract $manager, $callback);
}
