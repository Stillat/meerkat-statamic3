<?php

namespace Stillat\Meerkat\Core\Exceptions;

/**
 * Class InconsistentCompositionException
 *
 * Thrown when an object's state is detected
 * to be in an unreliable state at run time.
 *
 * @since 2.0.0
 */
class InconsistentCompositionException extends MeerkatCoreException
{
    /**
     * Creates a new instance of InconsistentCompositionException.
     *
     * @param  string  $propertyName The property name expected.
     * @param  string  $className The containing class name.
     * @return InconsistentCompositionException
     */
    public static function make($propertyName, $className)
    {
        return new InconsistentCompositionException($propertyName.' expected to be present on '.$className);
    }
}
