<?php

namespace Stillat\Meerkat\Core\Support;

/**
 * Class Env
 *
 * Provides information related to the execution environment and server.
 *
 * @since 2.0.0
 */
class Env
{
    /**
     * Indicates if the Addon is running on Windows.
     *
     * @return bool
     */
    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
