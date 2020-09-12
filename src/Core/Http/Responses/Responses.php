<?php

namespace Stillat\Meerkat\Core\Http\Responses;

class Responses
{

    const KEY_SUCCESS = 'success';
    const KEY_ERROR_CODE = 'error_code';
    const KEY_MESSAGE = 'msg';
    const KEY_RECOVERABLE = 'is_recoverable';

    /**
     * Creates a general error response.
     *
     * @param string $errorCode The error code.
     * @param bool $isRecoverable Indicates if the error was catastrophic or not.
     */
    public static function fromErrorCode($errorCode, $isRecoverable)
    {
        return [
            self::KEY_SUCCESS => false,
            self::KEY_ERROR_CODE => $errorCode,
            self::KEY_RECOVERABLE => $isRecoverable
        ];
    }

}
