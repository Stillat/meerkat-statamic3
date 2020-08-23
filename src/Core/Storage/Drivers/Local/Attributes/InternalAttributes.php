<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class InternalAttributes
 *
 * Provides a central location to resolve internal attributes.
 *
 * When comment data is being saved, all internal attributes are removed.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes
 * @since 2.0.0
 */
class InternalAttributes
{

    /**
     * A collection of internal attributes that should not be saved.
     *
     * @var array|null
     */
    protected static $internalAttributes = null;

    /**
     * Gets a list of internal attributes that should not be saved.
     *
     * @return array
     */
    public static function getInternalAttributes()
    {
        if (self::$internalAttributes === null) {
            self::$internalAttributes = [
                CommentContract::KEY_COMMENT_MARKDOWN,
                CommentContract::KEY_COMMENT_DATE_FORMATTED,
                CommentContract::KEY_LEGACY_COMMENT,
                CommentContract::KEY_COMMENT_DATE,
                CommentContract::KEY_DEPTH,
                CommentContract::KEY_IS_REPLY,
                CommentContract::KEY_IS_PARENT,
                CommentContract::KEY_IS_ROOT,
                CommentContract::KEY_ANCESTORS,
                CommentContract::KEY_DESCENDENTS,
                CommentContract::KEY_CHILDREN,
                CommentContract::KEY_PARENT,
                CommentContract::KEY_PARENT_ID,
                CommentContract::INTERNAL_CONTENT_TRUNCATED,
                CommentContract::INTERNAL_CONTEXT,
                CommentContract::INTERNAL_CONTENT_RAW,
                CommentContract::INTERNAL_PATH,
                CommentContract::INTERNAL_RESPONSE,
                CommentContract::INTERNAL_RESPONSE_PATH,
                CommentContract::INTERNAL_RESPONSE_ID,
                CommentContract::INTERNAL_RESPONSE_CONTEXT,
                CommentContract::INTERNAL_RESPONSE_HAS_REPLIES,
                CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION,
                CommentContract::INTERNAL_STRUCTURE_HAS_REPLIES,
                CommentContract::INTERNAL_ABSOLUTE_ROOT,
            ];
        }

        return self::$internalAttributes;
    }

}
