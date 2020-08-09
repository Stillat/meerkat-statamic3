<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class MigrateCommentsCommand
 *
 * Provides utilities for migrating comment data structures.
 *
 * @package Stillat\Meerkat\Console\Commands
 * @since 2.0.0
 */
class MigrateCommentsCommand extends Command
{

    protected $signature = 'meerkat:migrate-comments';

    protected $description = 'Updates all comments to the current data format.';

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

        $comments = [];
        $commentCount = 0;

        foreach ($threadIds as $thread) {
            foreach ($this->threadManager->getAllCommentsById($thread) as $comment) {
                if (TypeConversions::getBooleanValue(
                    $comment->getDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, false)
                )) {
                    $comments[] = $comment;
                }
                $commentCount += 1;
            }
        }

        $this->line('Total: ' . $commentCount . ' comments(s) across all threads analyzed.');
        $this->line(count($comments) . ' comment(s) need a data structure update.');

        $commentsUpdated = 0;

        foreach ($comments as $comment) {
            $updateResult = $comment->updateStructure();

            if ($updateResult === true) {
                $commentsUpdated += 1;
            }
        }

        $this->line($commentsUpdated . ' comment(s) updated!');

    }

}
