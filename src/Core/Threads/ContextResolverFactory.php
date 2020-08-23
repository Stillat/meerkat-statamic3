<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;

/**
 * Class ContextResolverFactory
 *
 * Provides Meerkat Core internals access to a shared instance of the CommentResolverContract.
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class ContextResolverFactory
{


    /**
     * A reference to the ContextResolverContract implementation instance.
     *
     * @var ContextResolverContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if an ContextResolverContract implementation instance was set.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (ContextResolverFactory::$instance === null) {
            return false;
        }

        if ((ContextResolverFactory::$instance instanceof ContextResolverContract) == false) {
            return false;
        }

        return true;
    }

}
