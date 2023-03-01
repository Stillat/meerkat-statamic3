<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;

class TaskController extends CpController
{
    public function getTaskStatus(TaskStorageManagerContract $taskManager)
    {
        $taskId = $this->request->get(ApiParameters::PARAM_TASK_ID, null);

        if ($taskId === null) {
            return Responses::recoverableFailure(Errors::TASK_NOT_FOUND);
        }

        $taskId = trim($taskId);

        if ($taskManager->existsById($taskId) === false) {
            return Responses::recoverableFailure(Errors::TASK_NOT_FOUND);
        }

        try {
            $task = $taskManager->findById($taskId);

            if ($task !== null) {
                return Responses::successWithData([
                    'task' => $task->getInstanceId(),
                    'status' => $task->getStatus(),
                ]);
            }
        } catch (Exception $e) {
            ErrorReporterFactory::report($e);

            return Responses::generalFailure();
        }

        return Responses::generalFailure();
    }
}
