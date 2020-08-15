<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;

/**
 * Class SanitationManagerFactory
 *
 * Provides a shared SanitationManagerContract implementation instance for static APIs.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
class SanitationManagerFactory
{

    /**
     * A shared SanitationManagerContract implementation instance.
     *
     * @var null|SanitationManagerContract
     */
    public static $instance = null;

    /**
     * Indicates if a shared SanitationManagerContract implementation exists.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (SanitationManagerFactory::$instance != null) {
            return true;
        }

        return false;
    }

}
