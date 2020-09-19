<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\CommentNotFoundException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Http\MessageGeneralCommentResponseGenerator;
use Stillat\Meerkat\Http\RequestHelpers;

class SpamController extends CpController
{

    public function markAsSpam(
        CommentStorageManagerContract $storageManager,
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager,
        MessageGeneralCommentResponseGenerator $resultGenerator,
        CommentResponseGenerator $commentResultGenerator)
    {
        $permissions = $manager->getPermissions($identityManager->getIdentityContext());

        if ($permissions->canReportAsSpam === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_REPORT_SPAM);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_REPORT_SPAM
                ]);
                exit;
            }
        }

        RequestHelpers::setActionFromRequest($this->request);

        $commentId = $this->request->get(ApiParameters::PARAM_COMMENT, null);

        if ($commentId === null) {
            return $resultGenerator->notFound('null');
        }

        try {
            $comment = Comment::findOrFail($commentId);

            $result = $storageManager->setIsSpam($comment);

            if ($result === true) {
                $comment = Comment::find($commentId);
            }

            return Responses::conditionalWithData($result, [
                ApiParameters::RESULT_COMMENT => $commentResultGenerator->getApiComment($comment->toArray())
            ]);
        } catch (CommentNotFoundException $notFound) {
            return $resultGenerator->notFound($commentId);
        } catch (Exception $e) {
            throw $e;
            return Responses::generalFailure();
        }
    }




}
