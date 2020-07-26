<?php

namespace Stillat\Meerkat\Core;

use Exception;

/**
 * Class MeerkatCoreException
 *
 * The base Meerkat Core exception.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
abstract class MeerkatCoreException extends Exception
{

    /**
     * A collection of optional error messages.
     *
     * @var array
     */
    protected $errors;

    /**
     * Sets the error messages.
     *
     * @param array $errors The error messages.
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Gets the error messages.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
