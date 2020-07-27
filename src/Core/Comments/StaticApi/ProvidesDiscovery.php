<?php

namespace Stillat\Meerkat\Core\Comments\StaticApi;

use Stillat\Meerkat\Core\Comments\CommentManagerFactory;
use Stillat\Meerkat\Core\Exceptions\CommentNotFoundException;

/**
 * Trait ProvidesDiscovery
 *
 * Provides a static comment-discovery API.
 *
 * @package Stillat\Meerkat\Core\Comments\StaticApi
 * @since 2.0.0
 */
trait ProvidesDiscovery
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

    /**
     * Attemps to locate the specified comment.
     *
     * @param string $commentId The comment's string identifier.
     * @return \Stillat\Meerkat\Core\Contracts\Comments\CommentContract
     * @throws CommentNotFoundException
     */
    public static function findOrFail($commentId)
    {
        $comment = self::find($commentId);

        if ($comment === null) {
            throw new CommentNotFoundException("Comment {$commentId} was not found.");
        }

        return $comment;
    }

}