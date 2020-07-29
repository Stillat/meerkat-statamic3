<?php

namespace Stillat\Meerkat\Core\Comments\StaticApi;

use Stillat\Meerkat\Core\Comments\CommentManagerFactory;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

trait ProvidesMutations
{

    /**
     * Sets up a comment as a reply to the provided parent.
     *
     * @param string $parentId The parent comment string identifier.
     * @param CommentContract $comment The child comment instance.
     * @return CommentContract|null
     */
    public static function replyTo($parentId, CommentContract $comment)
    {
        if (CommentManagerFactory::hasInstance()) {
            return CommentManagerFactory::$instance->replyTo($parentId, $comment);
        }

        return null;
    }

    public static function saveReplyTo($parentId, CommentContract $comment)
    {
        if (CommentManagerFactory::hasInstance()) {
            $results = CommentManagerFactory::$instance->saveReplyTo($parentId, $comment);

            if ($results == true ) {
                return CommentManagerFactory::$instance->findById($comment->getId());
            }
        }

        return false;
    }

}