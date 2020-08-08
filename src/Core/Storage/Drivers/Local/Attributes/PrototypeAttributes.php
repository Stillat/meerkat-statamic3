<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;

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
                AuthorContract::KEY_USER_AGENT,
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

}
