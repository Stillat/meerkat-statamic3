<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Concerns\InteractsWithInput;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Feedback\SolutionProvider;

/**
 * Class ErrorLogsController
 *
 * Provides the HTTP API for interacting with the Meerkat Core error code log.
 *
 * @package Stillat\Meerkat\Http\Controllers
 * @since 2.0.0
 */
class ErrorLogsController extends CpController
{
    use InteractsWithInput;

    /**
     * The ErrorCodeRepositoryContract implementation instance.
     *
     * @var ErrorCodeRepositoryContract
     */
    private $errors = null;

    /**
     * The SolutionProvider instance.
     *
     * @var SolutionProvider
     */
    private $solutions = null;

    public function __construct(ErrorCodeRepositoryContract $errorRepository, SolutionProvider $solutions)
    {
        $this->errors = $errorRepository;
        $this->solutions = $solutions;

        $this->solutions->setIsCli(false);
    }

    public function index()
    {
        Statamic::script('meerkat', 'meerkat-core');

        return view('meerkat::errors');
    }

    /**
     * Returns the current error logs.
     *
     * GET /meerkat/error-logs/logs
     *
     * @return array
     */
    public function getLogs()
    {
        $logsToReturn = [];
        $errorLogs = $this->errors->getLogs();

        foreach ($errorLogs as $log) {
            $logsToReturn[] = [
                'log' => $log,
                'suggest' => $this->solutions->findSolution($log->errorCode)
            ];
        }

        return [
          'logs' => $logsToReturn
        ];
    }

    /**
     * Removes all error logs.
     *
     * POST /meerkat/error-logs/remove-logs
     *
     * @return array
     */
    public function postRemoveAllLogs()
    {
        $success = $this->errors->removeLogs();

        return [
            'success' => $success
        ];
    }

    /**
     * Removes a specific error log instance.
     *
     * POST /meerkat/error-logs/remove-log-instance
     *
     * PARAM: REQUIRED STRING: instanceId
     *
     * @return array|bool[]
     */
    public function postRemoveLogInstance()
    {
        if ($this->has('instanceId')) {
            $instanceId = $this->input('instanceId', null);

            if ($instanceId !== null && mb_strlen(trim($instanceId)) > 0) {
                $success = $this->errors->removeInstance($instanceId);

                return [
                    'success' => $success
                ];
            }
        }

        return [
          'success' => false
        ];
    }

}
