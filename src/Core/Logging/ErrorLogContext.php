<?php

namespace Stillat\Meerkat\Core\Logging;

use Exception;

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

    const KEY_MSG = 'msg';
    const KEY_DETAILS = 'details';

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

    /**
     * Attempts to convert the string to an ErrorLogContext instance.
     *
     * @param string $value The value to convert.
     * @return ErrorLogContext
     */
    public static function fromString($value)
    {
        $contextToReturn = new ErrorLogContext();
        $contextToReturn->msg = $value;

        try {
            $decoded = (array)json_decode($value);

            if (array_key_exists(self::KEY_MSG, $decoded)) {
                $contextToReturn->msg = $decoded[self::KEY_MSG];
            }

            if (array_key_exists(self::KEY_DETAILS, $decoded)) {
                $contextToReturn->details = $decoded[self::KEY_DETAILS];
            }
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
        }

        return $contextToReturn;
    }

}
