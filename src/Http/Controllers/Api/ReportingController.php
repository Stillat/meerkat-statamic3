<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Exception;
use Statamic\Http\Controllers\CP\CpController;
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

    /**
     * Generates and returns an overview report.
     *
     * @param OverviewAggregator $aggregator The report aggregator.
     * @return array
     */
    public function getReportOverview(OverviewAggregator $aggregator)
    {
        try {
            $report = $aggregator->getReport()->toArray();

            return Responses::successWithData($report);
        } catch (Exception $e) {
            return Responses::generalFailure();
        }
    }

}
