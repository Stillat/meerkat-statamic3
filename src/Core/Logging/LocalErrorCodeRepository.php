<?php

namespace Stillat\Meerkat\Core\Logging;

use Exception;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;

/**
 * Class LocalErrorCodeRepository
 *
 * Provides an error code repository implementation for a local filesystem.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class LocalErrorCodeRepository implements ErrorCodeRepositoryContract
{

    /**
     * A shared LocalErrorCodeRepository instance.
     *
     * @var LocalErrorCodeRepository|null
     */
    public static $instance = null;
    /**
     * The local path to store error code logs.
     *
     * @var string
     */
    private $storageDirectory = '';

    public function __construct($storageDirectory)
    {
        $this->storageDirectory = $storageDirectory;
    }

    /**
     * Logs an error by code and message.
     *
     * @param string $errorCode The error code.
     * @param string $errorMessage The error message.
     */
    public static function logCodeMessage($errorCode, $errorMessage)
    {
        $log = ErrorLog::make($errorCode, $errorMessage);

        LocalErrorCodeRepository::log($log);
    }

    /**
     * Logs an error through the shared instance.
     *
     * @param ErrorLog $log The error to log.
     */
    public static function log(ErrorLog $log)
    {
        if (self::$instance !== null) {
            self::$instance->logError($log);
        }
    }

    /**
     * Logs an error code.
     *
     * @param ErrorLog $log The error information to log.
     * @return bool
     */
    public function logError(ErrorLog $log)
    {
        if ($log === null) {
            return false;
        }

        $logPath = $this->makePath($log);
        $content = serialize($log);

        $result = file_put_contents($logPath, $content);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Constructs a storage path for the provided error log.
     *
     * @param ErrorLog $log The error log to construct the path for.
     * @return string
     */
    private function makePath(ErrorLog $log)
    {
        $logPath = 'e' . $log->errorCode . '-' . $log->instanceId . '.json';

        return $this->storageDirectory . '/' . $logPath;
    }

    /**
     * Removes all error code logs.
     *
     * @return bool
     */
    public function removeLogs()
    {
        if (!file_exists($this->storageDirectory) || is_dir($this->storageDirectory) == false) {
            return false;
        }

        $wasSuccess = true;

        $logs = glob($this->storageDirectory . '/e*.json');

        if ($logs !== null && is_array($logs)) {
            foreach ($logs as $logPath) {
                $result = unlink($logPath);

                if ($result === false) {
                    $wasSuccess = false;
                }
            }
        }

        return $wasSuccess;
    }

    /**
     * Removes an error log instance.
     *
     * @param string $instanceId The instance to remove.
     * @return bool
     */
    public function removeInstance($instanceId)
    {
        if (!file_exists($this->storageDirectory) || is_dir($this->storageDirectory) == false) {
            return false;
        }

        $logs = glob($this->storageDirectory . '/e*' . $instanceId . '.json');

        if ($logs !== null && is_array($logs) && count($logs) == 1) {
            return unlink($logs[0]);
        }

        return false;
    }

    /**
     * Returns the logs for the provided action.
     *
     * @param string|null $actionId The action identifier.
     * @return ErrorLog[]
     */
    public function getActionLogs($actionId)
    {
        $logs = $this->getLogs();

        return array_values(array_filter($logs, function (ErrorLog $log) use ($actionId) {
            return $log->action === $actionId;
        }));
    }

    /**
     * Returns a collection of error logs.
     *
     * @return ErrorLog[]
     */
    public function getLogs()
    {
        if (!file_exists($this->storageDirectory) || is_dir($this->storageDirectory) == false) {
            return [];
        }

        $logs = glob($this->storageDirectory . '/e*.json');
        $logsToReturn = [];

        if ($logs !== null && is_array($logs)) {
            foreach ($logs as $logPath) {
                $fileContent = file_get_contents($logPath);

                try {
                    $restoredLog = unserialize($fileContent);

                    $logsToReturn[] = $restoredLog;
                } catch (Exception $e) {
                    ExceptionLoggerFactory::log($e);
                }
            }
        }

        return $logsToReturn;
    }

}
