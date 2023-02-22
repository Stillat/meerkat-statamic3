<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;

/**
 * Class CommentManagerFactory
 *
 * Allows Meerkat Core internals to reference a global implementation.
 *
 * @since 2.0.0
 */
class CommentManagerFactory
{
    /**
     * A reference to the CommentManager instance.
     *
     * @var CommentManagerContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if a comment manager instance was set.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (CommentManagerFactory::$instance === null) {
            return false;
        }

        if ((CommentManagerFactory::$instance instanceof CommentManagerContract) == false) {
            return false;
        }

        return true;
    }
}
