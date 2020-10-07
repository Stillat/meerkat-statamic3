<?php

namespace Stillat\Meerkat\Core\Reporting;

/**
 * Class OverviewReport
 *
 * Represents a comment overview data collection (pending, spam, published, and other counts).
 *
 * @package Stillat\Meerkat\Reporting
 * @since 2.0.0
 */
class OverviewReport
{
    const KEY_TOTAL = 'total';
    const KEY_THREAD_COUNT = 'total_threads';
    const KEY_NEEDS_MIGRATION = 'needs_migration';
    const KEY_IS_SPAM = 'is_spam';
    const KEY_IS_HAM = 'is_ham';
    const KEY_REQUIRES_REVIEW = 'requires_review';
    const KEY_IS_PUBLISHED = 'is_published';
    const KEY_PUBLISHED_AND_SPAM = 'published_and_spam';
    const KEY_PENDING = 'pending';
    const KEY_DELETED = 'is_deleted';
    const KEY_COMPLETION_TIME = 'completion_time';

    /**
     * The total number of comments in the system.
     *
     * @var int
     */
    public $total = 0;

    /**
     * The total number of threads with comments.
     *
     * @var int
     */
    public $totalThreads = 0;

    /**
     * The total number of comments requiring migration.
     *
     * @var int
     */
    public $needsMigration = 0;

    /**
     * The total number of comments marked as spam.
     *
     * @var int
     */
    public $isSpam = 0;

    /**
     * The total number of comments marked as not spam.
     *
     * @var int
     */
    public $isHam = 0;

    /**
     * The total number of comments without a spam status flag.
     *
     * @var int
     */
    public $requiresReview = 0;

    /**
     * The total number of published comments.
     *
     * @var int
     */
    public $isPublished = 0;

    /**
     * The total number of comments that are both published and marked as spam.
     *
     * @var int
     */
    public $publishedAndSpam = 0;

    /**
     * The total number of comments that do not have a published status set.
     *
     * @var int
     */
    public $pending = 0;

    /**
     * The total number of comments that have been soft deleted.
     *
     * Soft deleted comments will not contributed to the other metrics.
     *
     * @var int
     */
    public $softDeleted = 0;

    /**
     * The total time to generate the report.
     *
     * @var float
     */
    public $generationSeconds = 0;

    /**
     * Converts the report to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_DELETED => $this->softDeleted,
            self::KEY_TOTAL => $this->total,
            self::KEY_THREAD_COUNT => $this->totalThreads,
            self::KEY_NEEDS_MIGRATION => $this->needsMigration,
            self::KEY_IS_SPAM => $this->isSpam,
            self::KEY_IS_HAM => $this->isHam,
            self::KEY_REQUIRES_REVIEW => $this->requiresReview,
            self::KEY_IS_PUBLISHED => $this->isPublished,
            self::KEY_PUBLISHED_AND_SPAM => $this->publishedAndSpam,
            self::KEY_PENDING => $this->pending,
            self::KEY_COMPLETION_TIME => $this->generationSeconds
        ];
    }

}
