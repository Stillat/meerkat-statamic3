<?php

namespace Stillat\Meerkat\Core\Assertions;

use InvalidArgumentException;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;

// TODO: Documentation/maybe deprecation
/**
 * @since 2.0.0
 */
class TypeAssertions
{

    private static function makeTypeAssertionMessage($value, $parameterName, $qualifiedClassName)
    {
        $providedClassName = get_class($value);

        return "Argument passed to parameter {$parameterName} must be an instance of {$qualifiedClassName}. {$providedClassName} provided.";
    }

    /**
     * Asserts a provided value is an instance of CommentContract.
     *
     * @param mixed  $value
     * @param string $parameterName
     * @return void
     */
    public static function assertIsComment($value, $parameterName)
    {
        if ($value === null || ($value instanceof CommentContract) == false) {
            throw new InvalidArgumentException(TypeAssertions::makeTypeAssertionMessage($value, $parameterName, CommentContract::class));
        }
    }

    public static function assertIsSpamGuard($value, $parameterName)
    {
        if ($value === null || ($value instanceof SpamGuardContract) == false) {
            throw new InvalidArgumentException(TypeAssertions::makeTypeAssertionMessage($value, $parameterName, SpamGuardContract::class));
        }
    }

    public static function assertIsGuardConfiguration($value, $parameterName)
    {
        if ($value === null || ($value instanceof GuardConfiguration) == false) {
            throw new InvalidArgumentException(TypeAssertions::makeTypeAssertionMessage($value, $parameterName, GuardConfiguration::class));
        }
    }

    public static function assertIsMeerkatConfiguration($value, $parameterName)
    {
        if ($value === null || ($value instanceof Configuration) == false) {
            throw new InvalidArgumentException(TypeAssertions::makeTypeAssertionMessage($value, $parameterName, Configuration::class));
        }
    }

}
