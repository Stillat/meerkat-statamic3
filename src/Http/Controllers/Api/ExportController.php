<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Data\Export\CsvExporter;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;

class ExportController extends CpController
{
    use UsesTranslations;

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

        // TODO: Make dynamic at some point.
        $exportFields = [
            'comment.date',
            'author.name',
            'author.email',
            'author.user_agent',
            'author.user_ip',
            'author.referer',
            'comment.content',
            'comment.is_spam',
            'comment.published'
        ];

        $exportHeaders = [];

        foreach ($exportFields as $field) {
            $exportHeaders[] = $this->trans('fields.' . $field);
        }

        $csvExporter->setPropertyNames($exportHeaders);
        $csvExporter->setProperties($exportFields);

        $resultGenerator->updateFromParameters($this->request->all(), true);
        $comments = $resultGenerator->getRequestComments();
        $data = $csvExporter->export($comments);

        if ($this->request->has('download')) {
            $dir = storage_path('meerkat/tmp/downloads');

            if (!file_exists($dir)) {
                mkdir($dir, 0655, true);
            }

            $path = storage_path('meerkat/tmp/downloads/Comments-' . time() . '.csv');
            File::put($path, $data);

            $response = response()->download($path)->deleteFileAfterSend(true);
        } else {
            $response = response($data)->header('Content-Type', $csvExporter->getContentType());
        }

        return $response;
    }

}
