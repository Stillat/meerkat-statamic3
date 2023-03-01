<?php

namespace Stillat\Meerkat\Core\Logging;

/**
 * Class GlobalLogState
 *
 * Provides a central location to managing debug-level system variables.
 *
 * @since 2.3.0
 */
class GlobalLogState
{
    /**
     * Indicates if Meerkat is running in a debug environment.
     *
     * @var bool
     */
    public static $isApplicationInDebugMode = false;
}
