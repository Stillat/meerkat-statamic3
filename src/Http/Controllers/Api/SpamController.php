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
use Stillat\Meerkat\Core\Guard\SpamCleaner;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Http\MessageGeneralCommentResponseGenerator;
use Stillat\Meerkat\Http\RequestHelpers;

class SpamController extends CpController
{

    public function markManyAsSpam(
        CommentStorageManagerContract $storageManager,
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager)
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

        $commentIds = $this->request->get(ApiParameters::PARAM_COMMENTS, null);

        if ($commentIds === null || count($commentIds) === 0) {
            return Responses::conditionalWithData(false, [
                ApiParameters::RESULT_COMMENTS => []
            ]);
        }

        try {
            $result = $storageManager->setIsSpamForIds($commentIds);

            return Responses::conditionalWithData($result->success, [
                ApiParameters::RESULT_COMMENTS => $result->comments
            ]);
        } catch (Exception $e) {
            return Responses::generalFailure();
        }
    }

    public function markAsSpam(
        CommentStorageManagerContract $storageManager,
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager,
        MessageGeneralCommentResponseGenerator $resultGenerator,
        GuardConfiguration $guardConfiguration,
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
            $autoDeleted = false;
            $removedComments = [];

            if ($result === true) {
                $comment = Comment::find($commentId);

                if ($guardConfiguration->autoDeleteSpam === true) {

                    $removeResult = $storageManager->removeById($comment->getId());

                    if ($removeResult->success) {
                        $removedComments = $removeResult->comments;
                    }
                }
            }

            return Responses::conditionalWithData($result, [
                ApiParameters::RESULT_COMMENT => $commentResultGenerator->getApiComment($comment->toArray()),
                ApiParameters::RESULT_AUTO_REMOVED => $autoDeleted,
                ApiParameters::RESULT_COMMENTS => $removedComments
            ]);
        } catch (CommentNotFoundException $notFound) {
            return $resultGenerator->notFound($commentId);
        } catch (Exception $e) {
            return Responses::generalFailure();
        }
    }

    public function removeAllSpam(PermissionsManagerContract $manager, IdentityManagerContract $identityManager, SpamCleaner $cleaner)
    {
        $permissions = $manager->getPermissions($identityManager->getIdentityContext());

        if ($permissions->canRemoveComments === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_REMOVE);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_REMOVE
                ]);
                exit;
            }
        }

        try {
            $result = $cleaner->deleteAllSpam();

            return Responses::conditionalWithData($result->success, [
                ApiParameters::RESULT_REMOVED_IDS => $result->comments
            ]);
        } catch (Exception $e) {
            return Responses::generalFailure();
        }
    }


}
