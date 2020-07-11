<?php

namespace Stillat\Meerkat\Core\Guard;

/**
 * Class GuardResult
 *
 * Contains success status and error data from a guard operation.
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.0.0
 */
class GuardResult
{

    /**
     * A collection of errors raised during the guard operation.
     *
     * @var array The errors.
     */
    public $errors = [];

    /**
     * Indicates if the guard operation was a success.
     *
     * @var bool The success status.
     */
    public $success = false;

}