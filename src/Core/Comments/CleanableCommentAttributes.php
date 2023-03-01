<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class CleanableCommentAttributes
 *
 * Provides a central location to manage properties that should always be cleaned.
 *
 * @since 2.0.0
 */
class CleanableCommentAttributes
{
    /**
     * Caches the property array so we don't create multiple copies everywhere.
     *
     * @var array|null
     */
    protected static $attributesToClean = null;

    /**
     * Cleans up internal storable data attributes.
     *
     * @param  array  $data The data to clean.
     * @return array
     */
    public static function clean($data)
    {
        $cleanableProperties = self::getCleanableAttributes();

        foreach ($data as $attributeName => $value) {
            if (in_array($attributeName, $cleanableProperties)) {
                $value = ltrim($value, '"\' ');
                $value = rtrim($value, '"\' ');

                $data[$attributeName] = $value;
            }
        }

        return $data;
    }

    /**
     * Returns an array of data-attributes that should always be cleaned.
     *
     * @return array
     */
    public static function getCleanableAttributes()
    {
        if (self::$attributesToClean === null) {
            self::$attributesToClean = [
                CommentContract::KEY_ID,
                CommentContract::KEY_NAME,
                CommentContract::KEY_USER_IP,
                CommentContract::KEY_USER_AGENT,
                CommentContract::KEY_REFERRER,
                CommentContract::KEY_PAGE_URL,
            ];
        }

        return self::$attributesToClean;
    }
}
