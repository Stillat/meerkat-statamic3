<?php

namespace Stillat\Meerkat\Core\Http\Responses;

use Stillat\Meerkat\Core\Errors;

class Responses
{
    const KEY_SUCCESS = 'success';

    const KEY_ERROR_CODE = 'error_code';

    const KEY_MESSAGE = 'msg';

    const KEY_RECOVERABLE = 'is_recoverable';

    public static function generalFailure()
    {
        return self::fromErrorCode(Errors::GENERAL_EXCEPTION, false);
    }

    /**
     * Creates a general error response.
     *
     * @param  string  $errorCode The error code.
     * @param  bool  $isRecoverable Indicates if the error was catastrophic or not.
     */
    public static function fromErrorCode($errorCode, $isRecoverable)
    {
        return [
            self::KEY_SUCCESS => false,
            self::KEY_ERROR_CODE => $errorCode,
            self::KEY_RECOVERABLE => $isRecoverable,
        ];
    }

    public static function conditionalWithData($success, $data)
    {
        $baseData = self::generalSuccess();

        if ($success === false) {
            $baseData = self::nonFatalFailure();
        }

        return array_merge($baseData, $data);
    }

    public static function generalSuccess()
    {
        return [
            self::KEY_SUCCESS => true,
            self::KEY_ERROR_CODE => null,
            self::KEY_RECOVERABLE => true,
        ];
    }

    public static function nonFatalFailure()
    {
        return [
            self::KEY_SUCCESS => false,
            self::KEY_ERROR_CODE => null,
            self::KEY_RECOVERABLE => true,
        ];
    }

    public static function recoverableFailure($errorCode)
    {
        return self::fromErrorCode($errorCode, true);
    }

    public static function successWithData($data)
    {
        return array_merge(self::generalSuccess(), $data);
    }

    public static function failureWithData($data)
    {
        return array_merge(self::nonFatalFailure(), $data);
    }
}
