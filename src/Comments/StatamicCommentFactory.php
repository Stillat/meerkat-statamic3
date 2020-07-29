<?php

namespace Stillat\Meerkat\Comments;

use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\CommentManagerFactory;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;

/**
 * Class StatamicCommentFactory
 *
 * Allows for customizations in how comments are created from a Statamic installation.
 *
 * @package Stillat\Meerkat\Comments
 * @since 2.0.0
 */
class StatamicCommentFactory implements CommentFactoryContract
{

    /**
     * Converts the comment prototype into an instance of CommentContract.
     *
     * @param array $protoComment The comment prototype.
     * @return CommentContract
     */
    public function makeComment($protoComment)
    {
        $comment = new Comment();
        // Start: Comment class specific implementation details.
        $storageManager = CommentManagerFactory::$instance->getStorageManager();
        $comment->setStorageManager($storageManager);
        // End:   Comment class specific implementation details.

        $comment->setDataAttributes($protoComment);

        if (array_key_exists('comment', $protoComment)) {
            $comment->setRawContent($protoComment['comment']);
        }

        return $comment;
    }

}
