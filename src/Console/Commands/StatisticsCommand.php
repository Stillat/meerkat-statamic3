<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
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
     * The thread storage manager implementation instance.
     *
     * @var ThreadStorageManagerContract|null
     */
    protected $threadManager = null;

    public function __construct(ThreadStorageManagerContract $threadManager)
    {
        parent::__construct();

        $this->threadManager = $threadManager;
    }

    public function handle()
    {
        $threadIds = $this->threadManager->getAllThreadIds();

        $this->line('Discovered ' . count($threadIds) . ' thread(s) that will be analyzed.');

        $commentCount = 0;
        $needsMigration = 0;
        $isSpam = 0;
        $isPublished = 0;
        $publishedAndSpam = 0;
        $pendingCount = 0;

        $startTime = microtime(true);

        foreach ($threadIds as $thread) {
            foreach ($this->threadManager->getAllCommentsById($thread) as $comment) {
                if (TypeConversions::getBooleanValue(
                    $comment->getDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, false)
                )) {
                    $needsMigration += 1;
                }

                if ($comment->isSpam()) {
                    $isSpam += 1;
                }

                if ($comment->published()) {
                    $isPublished += 1;
                } else {
                    $pendingCount += 1;
                }

                if ($comment->isSpam() && $comment->published()) {
                    $publishedAndSpam += 1;
                }

                $commentCount += 1;
            }
        }

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
            count($threadIds),
            $commentCount,
            $isSpam,
            $publishedAndSpam,
            $isPublished,
            $pendingCount,
            $needsMigration
        ]]);

        $this->line('Statistics gathered in: ' . $secondsToGenerate . ' seconds.');
    }

}
