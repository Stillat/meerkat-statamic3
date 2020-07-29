<?php

namespace Stillat\Meerkat\Core\Comments\Static;

trait CommentStaticApi
{

    /**
     * Attempts to locate the specified comment.
     *
     * @param string $commentId The comment's string identifier.
     * @return \Stillat\Meerkat\Core\Contracts\Comments\CommentContract|null
     */
    public static function find($commentId)
    {
        if (CommentManagerFactory::hasInstance()) {
            return CommentManagerFactory::$instance->findById($commentId);
        }

        return null;
    }

    public static function findOrFail($commentId)
    {

    }

}