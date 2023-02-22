<?php

namespace Stillat\Meerkat\Core\Guard;

/**
 * Class SpamCheckerFactory
 *
 * Provides utilities for creating an instance of SpamChecker.
 *
 * @since 2.0.0
 */
class SpamCheckerFactory
{
    /**
     * A factory method that will return an instance of SpamChecker.
     *
     * @var callable|null
     */
    public static $factoryMethod = null;

    /**
     * Attempts to create a new instance of SpamChecker.
     *
     * @return SpamChecker|null
     */
    public static function getNew()
    {
        if (SpamCheckerFactory::$factoryMethod === null) {
            return null;
        }

        $factory = self::$factoryMethod;

        return $factory();
    }
}
