<?php

namespace Stillat\Meerkat\Core\Comments\StaticApi;

use Stillat\Meerkat\Core\Comments\CommentManagerFactory;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Trait ProvidesCreation
 *
 * Provides a static creation API for the comments.
 *
 * @package Stillat\Meerkat\Core\Comments\StaticApi
 * @since 2.0.0
 */
trait ProvidesCreation
{

    /**
     * Attempts to create a new comment from the array data.
     *
     * @param $data
     * @return CommentContract
     */
    public static function newFromArray($data)
    {
        if (CommentManagerFactory::hasInstance()) {
            if (array_key_exists(CommentContract::KEY_ID, $data) == false) {
                $data = [CommentContract::KEY_ID => time()] + $data;
            }

            $comment = CommentManagerFactory::$instance->getStorageManager()->makeFromArrayPrototype($data);
            $comment->setIsNew(true);
            $comment->setDataAttribute(CommentContract::KEY_ID, $data[CommentContract::KEY_ID]);

            return $comment;
        }

        return null;
    }

}
