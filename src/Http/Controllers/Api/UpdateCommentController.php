<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\CommentNotFoundException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Http\MessageGeneralCommentResponseGenerator;
use Stillat\Meerkat\Http\RequestHelpers;

class UpdateCommentController extends CpController
{

    public function updateComment(
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager,
        MessageGeneralCommentResponseGenerator $resultGenerator,
        CommentResponseGenerator $commentResultGenerator)
    {
        $permissions = $manager->getPermissions($identityManager->getIdentityContext());

        if ($permissions->canEditComments === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_EDIT);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_EDIT
                ]);
                exit;
            }
        }

        RequestHelpers::setActionFromRequest($this->request);

        $commentId = $this->request->get(ApiParameters::PARAM_COMMENT, null);
        $content = $this->request->get(ApiParameters::PARAM_COMMENT_CONTENT, null);

        if ($commentId === null) {
            return $resultGenerator->notFound('null');
        }

        try {
            $comment = Comment::findOrFail($commentId);

            $result = $comment->updateCommentContent($content);

            return Responses::conditionalWithData($result, [
                ApiParameters::RESULT_COMMENT => $commentResultGenerator->getApiComment($comment->toArray())
            ]);
        } catch (CommentNotFoundException $notFoundException) {
            ErrorReporterFactory::report($notFoundException);

            return $resultGenerator->notFound($commentId);
        } catch (Exception $e) {
            ErrorReporterFactory::report($e);

            return Responses::generalFailure();
        }
    }

}
