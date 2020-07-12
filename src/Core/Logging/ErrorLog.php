<?php

namespace Stillat\Meerkat\Core\Logging;

use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class ErrorLog
 *
 * Represents an error-code raising event in the Meerkat system.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class ErrorLog implements \Serializable
{

    const KEY_ID = 'id';
    const KEY_ERROR_CODE = 'ec';
    const KEY_CONTEXT = 'ctx';
    const KEY_DATE = 'date';

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
     * Creates a new instance of ErrorLog.
     *
     * @param string $errorCode The error code.
     * @param string $context The error's surrounding context.
     * @return ErrorLog
     */
    public static function make($errorCode, $context)
    {
        $errorLog = new ErrorLog();

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

        return $errorLog;
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
            self::KEY_DATE => $this->dateTimeUtc
        ];
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
     * Returns a run-time instance of an object from serialized form.
     *
     * @param string $serialized
     * @return static
     */
    public function unserialize($serialized)
    {
        $arrayFormat = (array)json_decode($serialized);

        $this->instanceId = $arrayFormat[self::KEY_ID];
        $this->errorCode = $arrayFormat[self::KEY_ERROR_CODE];
        $this->context = $arrayFormat[self::KEY_CONTEXT];
        $this->dateTimeUtc = $arrayFormat[self::KEY_DATE];
    }

}