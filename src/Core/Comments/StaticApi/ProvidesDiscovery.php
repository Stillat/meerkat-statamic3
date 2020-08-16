<?php

namespace Stillat\Meerkat\Core\Comments\StaticApi;

use Stillat\Meerkat\Core\Comments\CommentManagerFactory;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
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
     * @return CommentContract
     * @throws CommentNotFoundException
     */
    public static function findOrFail($commentId)
    {
        $comment = self::find($commentId);

        if ($comment === null) {
            $notFoundException = new CommentNotFoundException("Comment {$commentId} was not found.");
            $notFoundException->commentId = $commentId;

            throw $notFoundException;
        }

        return $comment;
    }

    /**
     * Attempts to locate the specified comment.
     *
     * @param string $commentId The comment's string identifier.
     * @return CommentContract|null
     */
    public static function find($commentId)
    {
        if (CommentManagerFactory::hasInstance()) {
            return CommentManagerFactory::$instance->findById($commentId);
        }

        return null;
    }

}
