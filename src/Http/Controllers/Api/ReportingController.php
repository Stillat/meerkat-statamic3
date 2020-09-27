<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Reporting\OverviewAggregator;

/**
 * Class ReportingController
 *
 * Provides communication between HTTP clients and the Meerkat server components.
 *
 * @package Stillat\Meerkat\Http\Controllers\Api
 * @since 2.0.0
 */
class ReportingController extends CpController
{

    public function getReportOverview(
        PermissionsManagerContract $manager,
        IdentityManagerContract $identityManager,
        OverviewAggregator $aggregator)
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
            $report = $aggregator->getReport()->toArray();

            return Responses::successWithData($report);
        } catch (Exception $e) {
            return Responses::generalFailure();
        }
    }

}
