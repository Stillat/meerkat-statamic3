<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class TruthyAttributes
 *
 * Provides a consistent location to resolve "truth" prototype elements.
 *
 * When a truthy attribute is discovered, it is automatically cast to a boolean type.
 *
 * @since 2.0.0
 */
class TruthyAttributes
{
    /**
     * Caches the internal truthy attributes.
     *
     * @var array
     */
    protected static $internalTruthyAttributes = null;

    /**
     * Gets a collection of internal truthy attributes.
     *
     * @return array
     */
    public static function getTruthyAttributes()
    {
        if (self::$internalTruthyAttributes === null) {
            self::$internalTruthyAttributes = [
                CommentContract::KEY_PUBLISHED,
                CommentContract::KEY_SPAM,
                CommentContract::KEY_IS_DELETED,
            ];
        }

        return self::$internalTruthyAttributes;
    }
}
