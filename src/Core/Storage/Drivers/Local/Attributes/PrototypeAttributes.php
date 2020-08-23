<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Data\Types;

/**
 * Class PrototypeAttributes
 *
 * Provides a consistent location to resolve comment prototype attributes.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes
 * @since 2.0.0
 */
class PrototypeAttributes
{

    /**
     * A cache of prototype attributes.
     *
     * @var array
     */
    protected static $prototypeAttributes = null;

    protected static $prototypeExpectedTypes = null;

    /**
     * A list of comment prototype elements when parsing comment data.
     *
     * @return array
     */
    public static function getPrototypeAttributes()
    {
        if (self::$prototypeAttributes === null) {
            self::$prototypeAttributes = [
                AuthorContract::KEY_NAME,
                AuthorContract::KEY_EMAIL_ADDRESS,
                AuthorContract::KEY_USER_IP,
                CommentContract::KEY_ID,
                CommentContract::KEY_PUBLISHED,
                CommentContract::KEY_USER_AGENT,
                CommentContract::KEY_REFERRER,
                CommentContract::KEY_IS_DELETED,
                CommentContract::KEY_PAGE_URL,
                CommentContract::KEY_SPAM,
                AuthorContract::AUTHENTICATED_USER_ID
            ];
        }

        return self::$prototypeAttributes;
    }

    /**
     * Returns a list of comment prototype expected runtime types.
     *
     * @return array
     */
    public static function getPrototypeExpectedTypes()
    {
        if (self::$prototypeExpectedTypes === null) {
            self::$prototypeExpectedTypes = [
                AuthorContract::KEY_NAME => Types::TYPE_STRING,
                AuthorContract::KEY_EMAIL_ADDRESS => Types::TYPE_STRING,
                AuthorContract::KEY_USER_IP => Types::TYPE_STRING,
                AuthorContract::KEY_USER_AGENT => Types::TYPE_STRING,
                CommentContract::KEY_ID => Types::TYPE_STRING,
                CommentContract::KEY_PUBLISHED => Types::TYPE_BIT,
                CommentContract::KEY_REFERRER => Types::TYPE_STRING,
                CommentContract::KEY_IS_DELETED => Types::TYPE_BIT,
                CommentContract::KEY_PAGE_URL => Types::TYPE_STRING,
                CommentContract::KEY_SPAM => Types::TYPE_BIT,
                AuthorContract::AUTHENTICATED_USER_ID => Types::TYPE_STRING
            ];
        }

        return self::$prototypeExpectedTypes;
    }

}
