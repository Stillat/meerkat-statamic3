<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Validation\RouteCacheValidator;

/**
 * Trait CanCorrectRoutes
 * @package Stillat\Meerkat\Concerns
 * @since 2.2.2
 */
trait CanCorrectRoutes
{

    /**
     * Attempts to automatically clear the Laravel route cache, if allowed.
     */
    protected function attemptToCorrectRoutes()
    {
        if (config('meerkat.internals.canAttemptToAutoCorrectRoutingIssues', true)) {
            RouteCacheValidator::attemptToCorrectRoutes();
        }
    }

}
