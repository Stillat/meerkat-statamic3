<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Reporting\OverviewAggregator;
use Stillat\Meerkat\Core\Reporting\OverviewReport;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class StatisticsCommand
 *
 * Provides utilities for visualizing comment statistics.
 *
 * @package Stillat\Meerkat\Console\Commands
 * @since 2.0.0
 */
class StatisticsCommand extends Command
{

    protected $signature = 'meerkat:statistics';

    protected $description = 'Gathers Meerkat comment statistics.';

    /**
     * The OverviewAggregator instance.
     *
     * @var OverviewAggregator
     */
    protected $overviewAggregator = null;

    public function __construct(OverviewAggregator $aggregator)
    {
        parent::__construct();

        $this->overviewAggregator = $aggregator;
    }

    public function handle()
    {
        // TODO: Translation support.

        $startTime = microtime(true);

        $report = $this->overviewAggregator->getReport();

        $secondsToGenerate = microtime(true) - $startTime;

        $this->table([
            'Threads',
            'Total Comments',
            'Spam',
            'Spam & Published',
            'Published',
            'Pending',
            'Requires Migration'
        ], [[
            $report->totalThreads,
            $report->total,
            $report->isSpam,
            $report->publishedAndSpam,
            $report->isPublished,
            $report->pending,
            $report->needsMigration
        ]]);

        $this->line('Statistics gathered in: ' . $secondsToGenerate . ' seconds.');
    }

}
