<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Data\Export\CsvExporter;
use Stillat\Meerkat\Core\Data\Export\JsonExporter;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\ExportFields;

class ExportController extends CpController
{
    use UsesTranslations;


    // TODO: Check for exceptions on export and log to error logs.

    /**
     * The fields to export.
     *
     * TODO: make dynamic/configurable at some point.
     *
     * @var string[]
     */
    protected $exportFields = [];

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->exportFields = ExportFields::getExportFields();
    }

    public function json(JsonExporter $jsonExporter, PermissionsManagerContract $manager, IdentityManagerContract $identityManager,
                         CommentResponseGenerator $resultGenerator)
    {
        $permissions = $manager->getPermissions($identityManager->getIdentityContext());

        if ($permissions->canViewComments === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_VIEW);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_VIEW
                ]);
                exit;
            }
        }

        $data = '';

        try {
            $jsonExporter->setProperties($this->exportFields);
            $resultGenerator->updateFromParameters($this->request->all());
            $comments = $resultGenerator->getRequestComments();
            $data = $jsonExporter->export($comments);
        } catch (FilterException $filterException) {
            LocalErrorCodeRepository::logCodeMessage(Errors::EXPORT_FILTER_FAILURE, $filterException->getMessage());
            ExceptionLoggerFactory::log($filterException);
        } catch (Exception $exception) {
            LocalErrorCodeRepository::logCodeMessage(Errors::EXPORT_GENERAL_FAILURE, $exception->getMessage());
            ExceptionLoggerFactory::log($exception);
        }

        return $this->getResponse($data, 'json', $jsonExporter->getContentType());
    }

    private function getResponse($data, $extension, $type)
    {
        if ($this->request->has('download')) {
            $dir = storage_path('meerkat/tmp/downloads');

            if (!file_exists($dir)) {
                mkdir($dir, Paths::$directoryPermissions, true);
            }

            $path = storage_path('meerkat/tmp/downloads/Comments-' . time() . '.' . $extension);
            File::put($path, $data);

            $response = response()->download($path)->deleteFileAfterSend(true);
        } else {
            $response = response($data)->header('Content-Type', $type);
        }

        return $response;
    }

    public function csv(CsvExporter $csvExporter, PermissionsManagerContract $manager, IdentityManagerContract $identityManager,
                        CommentResponseGenerator $resultGenerator)
    {
        $permissions = $manager->getPermissions($identityManager->getIdentityContext());

        if ($permissions->canViewComments === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_VIEW);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_VIEW
                ]);
                exit;
            }
        }

        $exportHeaders = [];

        foreach ($this->exportFields as $field) {
            $exportHeaders[] = $this->trans('fields.' . $field);
        }

        $data = '';

        try {
            $csvExporter->setPropertyNames($exportHeaders);
            $csvExporter->setProperties($this->exportFields);

            $resultGenerator->updateFromParameters($this->request->all());
            $comments = $resultGenerator->getRequestComments();
            $data = $csvExporter->export($comments);
        } catch (FilterException $filterException) {
            LocalErrorCodeRepository::logCodeMessage(Errors::EXPORT_FILTER_FAILURE, $filterException->getMessage());
            ExceptionLoggerFactory::log($filterException);
        } catch (Exception $exception) {
            LocalErrorCodeRepository::logCodeMessage(Errors::EXPORT_GENERAL_FAILURE, $exception->getMessage());
            ExceptionLoggerFactory::log($exception);
        }

        return $this->getResponse($data, 'csv', $csvExporter->getContentType());
    }

}
