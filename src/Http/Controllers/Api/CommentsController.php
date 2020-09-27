<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Http\Responses\CommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;

class CommentsController extends CpController
{

    public function search(PermissionsManagerContract $manager,
                           IdentityManagerContract $identityManager,
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

        try {
            $resultGenerator->updateFromParameters($this->request->all());

            return Responses::successWithData($resultGenerator->getApiResponse());
        } catch (FilterException $filterException) {
            dd($filterException);
            return Responses::fromErrorCode(Errors::COMMENT_DATA_FILTER_FAILURE, false);
        } catch (Exception $e) {
            //throw $e;
            dd($e);
            return Responses::generalFailure();
        }
    }

}
