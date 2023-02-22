<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class MigrateCommentsCommand
 *
 * Provides utilities for migrating comment data structures.
 *
 * @since 2.0.0
 */
class MigrateCommentsCommand extends Command
{
    use UsesTranslations;

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

        $this->line($this->trans('commands.migrate_analyze_threads', [
            'threads' => count($threadIds),
        ]));

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

        $this->line($this->trans('commands.migrate_analyzed_count', [
            'comments' => $commentCount,
        ]));
        $this->line($this->trans('commands.migrate_structure_update_needed', [
            'comments' => count($comments),
        ]));

        $commentsUpdated = 0;

        foreach ($comments as $comment) {
            $updateResult = $comment->updateStructure();

            if ($updateResult === true) {
                $commentsUpdated += 1;
            }
        }

        $this->line($this->trans('commands.migrate_comments_updated', [
            'comments' => $commentsUpdated,
        ]));
    }
}
