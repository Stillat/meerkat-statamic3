<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Validation\RouteCacheValidator;

/**
 * Trait CanCorrectRoutes
 *
 * @since 2.2.3
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
