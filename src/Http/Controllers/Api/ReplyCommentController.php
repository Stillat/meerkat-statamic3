<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\CommentNotFoundException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Http\MessageGeneralCommentResponseGenerator;

class ReplyCommentController extends CpController
{
    /**
     * Attaches a comment reply from the Control Panel API request.
     *
     * This method is a slimmed down version of the Socialize Controller and is only
     * intended to be used in connection with the Control Panel reply action.
     */
    public function reply(
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager,
        MessageGeneralCommentResponseGenerator $resultGenerator,
        CommentResponseGenerator $commentResultGenerator)
    {
        $currentIdentity = $identityManager->getIdentityContext();
        $permissions = $manager->getPermissions($currentIdentity);

        if ($permissions->canReplyToComments === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_REPLY);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_REPLY,
                ]);
                exit;
            }
        }

        $replyingTo = $this->request->get(ApiParameters::PARAM_REPLYING_TO, null);
        $newContent = $this->request->get(ApiParameters::PARAM_COMMENT, null);

        if ($replyingTo === null) {
            return $resultGenerator->notFound('null');
        }

        try {
            $parentComment = Comment::findOrFail($replyingTo);
            $result = Comment::saveReplyTo($replyingTo, Comment::newFromArray([
                CommentContract::KEY_LEGACY_COMMENT => $newContent,
                AuthorContract::AUTHENTICATED_USER_ID => $currentIdentity->getId(),
                AuthorContract::KEY_NAME => $currentIdentity->getDisplayName(),
                AuthorContract::KEY_EMAIL_ADDRESS => $currentIdentity->getEmailAddress(),
                AuthorContract::KEY_USER_IP => $this->request->ip(),
                AuthorContract::KEY_USER_AGENT => $this->request->userAgent(),
                CommentContract::KEY_REFERRER => $this->request->headers->get('referer'),
            ]));

            if ($result === false) {
                return Responses::nonFatalFailure();
            }

            return Responses::successWithData([
                ApiParameters::RESULT_COMMENT => $commentResultGenerator->getApiComment($result->toArray()),
            ]);
        } catch (CommentNotFoundException $notFoundException) {
            ErrorReporterFactory::report($notFoundException);

            return $resultGenerator->notFound($replyingTo);
        } catch (Exception $e) {
            ErrorReporterFactory::report($e);

            return Responses::generalFailure();
        }
    }
}
