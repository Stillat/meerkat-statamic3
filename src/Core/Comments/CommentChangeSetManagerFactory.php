<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;

/**
 * Class CommentManagerFactory
 *
 * Allows Meerkat Core internals to reference a global implementation.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class CommentChangeSetManagerFactory
{
    /**
     * A reference to the CommentChangeSetStorageManagerContract instance.
     *
     * @var CommentChangeSetStorageManagerContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if a comment change set manager instance was set.
     *
     * @return boolean
     */
    public static function hasInstance()
    {
        if (CommentChangeSetManagerFactory::$instance === null) {
            return false;
        }

        if ((CommentChangeSetManagerFactory::$instance instanceof CommentChangeSetStorageManagerContract) == false) {
            return false;
        }

        return true;
    }

}
