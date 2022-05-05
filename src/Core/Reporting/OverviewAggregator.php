<?php

namespace Stillat\Meerkat\Core\Reporting;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class OverviewAggregator
 *
 * Provides comment aggregate reporting features (such as getting pending count, published count, etc.)
 *
 * @package Stillat\Meerkat\Reporting
 * @since 2.0.0
 */
class OverviewAggregator
{

    /**
     * The ThreadStorageManagerContract implementation instance.
     *
     * @var ThreadStorageManagerContract
     */
    protected $threadManager = null;

    public function __construct(ThreadStorageManagerContract $threadManager)
    {
        $this->threadManager = $threadManager;
    }

    /**
     * Generates the overview report and returns the results.
     *
     * @return OverviewReport
     */
    public function getReport()
    {
        $overviewReport = new OverviewReport();

        $startTime = microtime(true);

        $threadIds = $this->threadManager->getAllThreadIds(false);

        foreach ($threadIds as $thread) {
            $overviewReport->totalThreads += 1;

            $threadContext = $this->threadManager->findById($thread);

            if ($threadContext === null || $threadContext->getContext() === null) {
                continue;
            }

            /** @var CommentContract $comment */
            foreach ($this->threadManager->getAllCommentsById($thread) as $comment) {
                if (TypeConversions::getBooleanValue(
                    $comment->getDataAttribute(CommentContract::INTERNAL_PARSER_CONTENT_SUPPLEMENTED, false)
                )) {
                    continue;
                }

                if ($comment->isDeleted()) {
                    $overviewReport->softDeleted += 1;
                    continue;
                }
                $overviewReport->total += 1;

                if (TypeConversions::getBooleanValue(
                    $comment->getDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, false)
                )) {
                    $overviewReport->needsMigration += 1;
                }

                if ($comment->hasBeenCheckedForSpam()) {
                    if ($comment->isSpam()) {
                        $overviewReport->isSpam += 1;

                        if ($comment->published()) {
                            $overviewReport->publishedAndSpam += 1;
                        }
                    } else {
                        $overviewReport->isHam += 1;
                    }
                } else {
                    $overviewReport->requiresReview += 1;
                }

                if ($comment->published()) {
                    $overviewReport->isPublished += 1;
                } else {
                    if ($comment->hasBeenCheckedForSpam()) {
                        if ($comment->isSpam() === false) {
                            $overviewReport->pending += 1;
                        }
                    } else {
                        $overviewReport->pending += 1;
                    }
                }
            }
        }


        $secondsToGenerate = microtime(true) - $startTime;

        $overviewReport->generationSeconds = $secondsToGenerate;

        return $overviewReport;
    }

}
