<?php

namespace Stillat\Meerkat\Core\Logging;

/**
 * Class ErrorLogContext
 *
 * Provides a consistent API for interacting with Meerkat Error Log contexts.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class ErrorLogContext
{

    /**
     * The message generated at the time of the error.
     *
     * @var string
     */
    public $msg = '';

    /**
     * Additional context details, if available.
     *
     * @var string
     */
    public $details = '';

}