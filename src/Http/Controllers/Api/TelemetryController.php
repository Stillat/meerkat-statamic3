<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\API\ApiController;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Core\Logging\Telemetry;
use Stillat\Meerkat\Logging\ErrorLogPresenter;

class TelemetryController extends ApiController
{
    use UsesConfig;

    const KEY_ACTION = 'action';

    const KEY_REPORT = 'report';

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
        $this->telemetry = $telemetry;
        $this->errors = $errors;
    }

    public function submitReport(ErrorLogPresenter $presenter)
    {
        if ($this->getConfig('telemetry.enabled', true) === false) {
            return Responses::recoverableFailure(Errors::TELEMETRY_DISABLED);
        }

        $actionId = $this->request->input(self::KEY_ACTION, null);

        if ($actionId === null) {
            return Responses::recoverableFailure(Errors::TELEMETRY_MISSING_ACTION);
        }

        try {
            $logs = $this->errors->getActionLogs($actionId);

            if (is_array($logs) && count($logs) > 0) {
                $report = $presenter->present($logs[0]);

                $this->telemetry->sendReport($report);

                return Responses::generalSuccess();
            }
        } catch (Exception $e) {
            ErrorReporterFactory::report($e);

            return Responses::fromErrorCode(Errors::TELEMETRY_OPERATION_FAILURE, false);
        }

        return Responses::generalSuccess();
    }

    public function getReport(ErrorLogPresenter $presenter)
    {
        $actionId = $this->request->input(self::KEY_ACTION, null);

        if ($actionId === null) {
            return Responses::failureWithData([self::KEY_REPORT => null]);
        }

        try {
            $logs = $this->errors->getActionLogs($actionId);

            if (is_array($logs) && count($logs) > 0) {
                $report = $presenter->present($logs[0]);

                return Responses::successWithData([
                    self::KEY_REPORT => $report,
                ]);
            }
        } catch (Exception $e) {
            ErrorReporterFactory::report($e);

            return Responses::fromErrorCode(Errors::TELEMETRY_OPERATION_FAILURE, false);
        }

        return Responses::nonFatalFailure();
    }
}
