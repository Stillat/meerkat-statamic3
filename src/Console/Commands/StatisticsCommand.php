<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Reporting\OverviewAggregator;

/**
 * Class StatisticsCommand
 *
 * Provides utilities for visualizing comment statistics.
 *
 * @since 2.0.0
 */
class StatisticsCommand extends Command
{
    use UsesTranslations;

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
        $startTime = microtime(true);

        $report = $this->overviewAggregator->getReport();

        $secondsToGenerate = microtime(true) - $startTime;

        $this->table([
            $this->trans('commands.stat_threads'),
            $this->trans('commands.stat_total_comments'),
            $this->trans('commands.stat_spam'),
            $this->trans('commands.stat_spam_and_published'),
            $this->trans('commands.stat_published'),
            $this->trans('commands.stat_pending'),
            $this->trans('commands.stat_requires_migration'),
        ], [[
            $report->totalThreads,
            $report->total,
            $report->isSpam,
            $report->publishedAndSpam,
            $report->isPublished,
            $report->pending,
            $report->needsMigration,
        ]]);

        $this->line($this->trans('commands.stat_generated_in', [
            'seconds' => $secondsToGenerate,
        ]));
    }
}
