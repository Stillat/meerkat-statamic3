<?php

namespace Stillat\Meerkat\Core\Identity;

use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;

/**
 * Class IdentityManagerFactory
 *
 * Allows Meerkat Core internals to reference a global implementation.
 *
 * @package Stillat\Meerkat\Core\Identity
 * @since 2.0.0
 */
class IdentityManagerFactory
{

    /**
     * A reference to the IdentityManagerContract implementation instance.
     *
     * @var IdentityManagerContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if an IdentityManagerContract implementation instance was set.
     * @return bool
     */
    public static function hasInstance()
    {
        if (IdentityManagerFactory::$instance === null) {
            return false;
        }

        if ((IdentityManagerFactory::$instance instanceof IdentityManagerContract) == false) {
            return false;
        }

        return true;
    }

}

