<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Logging\Telemetry;
use Stillat\Meerkat\Logging\ErrorLogPresenter;

class TelemetryController extends ApiController
{
    use UsesConfig;

    /**
     * The Telemetry instance.
     *
     * @var Telemetry
     */
    protected $telemetry = null;

    /**
     * The ErrorCodeRepositoryContract implementation instance.
     *
     * @var ErrorCodeRepositoryContract
     */
    protected $errors = null;

    public function __construct(Telemetry $telemetry, ErrorCodeRepositoryContract $errors, Request $request)
    {
        parent::__construct($request);

        $this->telemetry = $telemetry;
        $this->errors = $errors;
    }

    public function submitReport(ErrorLogPresenter $presenter)
    {
        if ($this->getConfig('telemetry.enabled', true) === false) {
            return [
                'success' => false,
            ];
        }

        $actionId = $this->request->input('action', null);

        if ($actionId === null) {
            return [
                'success' => false,
            ];
        }

        try {
            $logs = $this->errors->getActionLogs($actionId);

            if (is_array($logs) && count($logs) > 0) {
                $report = $presenter->present($logs[0]);

                $this->telemetry->sendReport($report);
                return [
                    'success' => true
                ];
            }

        } catch (Exception $e) {
            dd($e);
            // TODO: CHECK FOR debug and retrhow.
        }

        return [
            'success' => false
        ];
    }

    public function getReport(ErrorLogPresenter $presenter)
    {
        $actionId = $this->request->input('action', null);

        if ($actionId === null) {
            return [
                'success' => false,
                'report' => null
            ];
        }

        try {
            $logs = $this->errors->getActionLogs($actionId);

            if (is_array($logs) && count($logs) > 0) {
                $report = $presenter->present($logs[0]);

                return [
                    'success' => true,
                    'report' => $report
                ];
            }

        } catch (Exception $e) {
            dd($e);
            // TODO: CHECK FOR debug and retrhow.
        }

        return [
            'success' => false,
            'report' => null
        ];
    }

    public function index()
    {
        $this->telemetry->sendReport('Hello, testing from inside Meerkat');
    }

}
