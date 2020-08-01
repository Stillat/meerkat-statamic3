<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

// TODO: Use later; just a placeholder.
interface SanitationMutationPipelineContract extends MutationPipelineContract
{

    const MUTATION_REGISTERING = 'sanitizer.registering';

    public function registeringSanitizers(SanitationManagerContract $manager, $callback);

}
