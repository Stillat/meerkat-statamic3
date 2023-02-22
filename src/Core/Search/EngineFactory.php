<?php

namespace Stillat\Meerkat\Core\Search;

/**
 * Class EngineFactory
 *
 * Provides a consistent location for interacting with the search sub-system.
 *
 * @since 2.0.0
 */
class EngineFactory
{
    /**
     * The search Engine instance, if any.
     *
     * @var null|Engine
     */
    public static $searchEngine = null;

    /**
     * Indicates if a search Engine instance has been supplied.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (EngineFactory::$searchEngine === null) {
            return false;
        }

        return true;
    }
}
