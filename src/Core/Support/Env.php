<?php

namespace Stillat\Meerkat\Core\Support;

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