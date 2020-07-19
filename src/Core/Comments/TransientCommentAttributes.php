<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class TransientCommentAttributes
 *
 * Provides a central location to manage properties that should not be persisted to disk.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class TransientCommentAttributes
{

    /**
     * Caches the property array so we don't create multiple copies everywhere.
     *
     * @var array|null
     */
    protected static $transientPropertyCache = null;

    /**
     * Returns an array of data-attributes that should not be persisted.
     *
     * @return array
     */
    public static function getTransientProperties()
    {
        if (self::$transientPropertyCache === null) {
            self::$transientPropertyCache = [
                CommentContract::KEY_REPLIES,
                CommentContract::KEY_COMMENT_DATE_FORMATTED,
                CommentContract::KEY_LEGACY_COMMENT,
                CommentContract::KEY_COMMENT_MARKDOWN,
                CommentContract::KEY_COMMENT_DATE,
                CommentContract::KEY_IS_REPLY,
                CommentContract::KEY_DEPTH,
                CommentContract::KEY_ANCESTORS,
                CommentContract::KEY_CHILDREN,
                CommentContract::KEY_PARENT,
                CommentContract::KEY_IS_PARENT,
                CommentContract::INTERNAL_CONTENT_TRUNCATED,
                CommentContract::INTERNAL_CONTENT_RAW,
                CommentContract::INTERNAL_PATH,
                CommentContract::INTERNAL_RESPONSE,
                CommentContract::INTERNAL_RESPONSE_PATH,
                CommentContract::INTERNAL_RESPONSE_CONTEXT,
                CommentContract::INTERNAL_RESPONSE_HAS_REPLIES,
                CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION
            ];
        }

        return self::$transientPropertyCache;
    }

}