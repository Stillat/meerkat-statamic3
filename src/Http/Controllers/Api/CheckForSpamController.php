<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Guard\SpamChecker;

class CheckForSpamController extends CpController
{

    public function checkForSpam(PermissionsManagerContract $manager,
                                 IdentityManagerContract $identityManager,
                                 SpamChecker $spamChecker)
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

        if ($permissions->canReportAsHam === false) {
            if ($this->request->ajax()) {
                return response('Unauthorized.', 401)->header('Meerkat-Permission', Errors::MISSING_PERMISSION_CAN_REPORT_HAM);
            } else {
                abort(403, 'Unauthorized', [
                    'Meerkat-Permission' => Errors::MISSING_PERMISSION_CAN_REPORT_HAM
                ]);
                exit;
            }
        }

        // TODO: Create a "task" id, return that as part of the response.
        //       After request,
        dd($spamChecker->check());
    }

}
