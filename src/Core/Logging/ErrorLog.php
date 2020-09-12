<?php

namespace Stillat\Meerkat\Core\Logging;

use Serializable;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class ErrorLog
 *
 * Represents an error-code raising event in the Meerkat system.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class ErrorLog implements Serializable
{

    const KEY_ID = 'id';
    const KEY_ERROR_CODE = 'ec';
    const KEY_CONTEXT = 'ctx';
    const KEY_DATE = 'date';
    const KEY_TYPE = 'type';
    const KEY_ACTION = 'action';
    const TYPE_ERROR = 0;
    const TYPE_WARNING = 1;
    const TYPE_MESSAGE = 3;
    public static $currentActionId = null;
    /**
     * The log's instance identifier.
     *
     * @var string
     */
    public $instanceId = '';

    /**
     * The log's error code.
     *
     * @var string
     */
    public $errorCode = '';

    /**
     * The error's surrounding context.
     *
     * @var string
     */
    public $context = '';

    /**
     * The date/time UTC that the error occurred.
     *
     * @var int
     */
    public $dateTimeUtc = '';

    /**
     * The error log type (warning, error, or message).
     *
     * @var int
     */
    public $type = self::TYPE_ERROR;

    /**
     * The action identifier, if available.
     *
     * @var string|null
     */
    public $action = null;

    /**
     * Creates a new warning instance of ErrorLog.
     *
     * @param string $errorCode The error code.
     * @param string $context The error's surrounding context.
     * @return ErrorLog
     */
    public static function warning($errorCode, $context)
    {
        $errorLog = ErrorLog::make($errorCode, $context);

        $errorLog->type = self::TYPE_WARNING;

        return $errorLog;
    }

    /**
     * Creates a new instance of ErrorLog.
     *
     * @param string $errorCode The error code.
     * @param string $context The error's surrounding context.
     * @return ErrorLog
     */
    public static function make($errorCode, $context)
    {
        $errorLog = new ErrorLog();

        $errorLog->type = self::TYPE_ERROR;
        $errorLog->instanceId = UuidGenerator::getInstance()->newId();

        if (is_string($context)) {
            $errorLogContext = new ErrorLogContext();
            $errorLogContext->msg = $context;

            $context = $errorLogContext;
        }

        if ($context instanceof ErrorLogContext) {
            $context = json_encode($context);
        }

        $errorLog->context = $context;
        $errorLog->errorCode = $errorCode;
        $errorLog->dateTimeUtc = time();
        $errorLog->action = ErrorLog::$currentActionId;

        return $errorLog;
    }

    /**
     * Creates a new message instance of ErrorLog.
     *
     * @param string $errorCode The error code.
     * @param string $context The error's surrounding context.
     * @return ErrorLog
     */
    public static function message($errorCode, $context)
    {
        $errorLog = ErrorLog::make($errorCode, $context);

        $errorLog->type = self::TYPE_MESSAGE;

        return $errorLog;
    }

    /**
     * Returns a string representation of the current error log.
     *
     * @return false|string
     */
    public function serialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the error log to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_ID => $this->instanceId,
            self::KEY_ERROR_CODE => $this->errorCode,
            self::KEY_CONTEXT => $this->context,
            self::KEY_DATE => $this->dateTimeUtc,
            self::KEY_TYPE => $this->type,
            self::KEY_ACTION => $this->action
        ];
    }

    /**
     * Returns a run-time instance of an object from serialized form.
     *
     * @param string $serialized The serialized contents.
     */
    public function unserialize($serialized)
    {
        $arrayFormat = (array)json_decode($serialized);

        $this->instanceId = $arrayFormat[self::KEY_ID];
        $this->errorCode = $arrayFormat[self::KEY_ERROR_CODE];
        $this->context = ErrorLogContext::fromString($arrayFormat[self::KEY_CONTEXT]);
        $this->dateTimeUtc = $arrayFormat[self::KEY_DATE];
        $this->type = $arrayFormat[self::KEY_TYPE];
        $this->action = $arrayFormat[self::KEY_ACTION];
    }

}
