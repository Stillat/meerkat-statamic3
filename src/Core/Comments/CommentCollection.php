<?php

namespace Stillat\Meerkat\Core\Comments;

/**
 * Class CommentCollection
 *
 * Provides a consistent search result container.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class CommentCollection
{

    /**
     * A collection of materialized Meerkat comments.
     *
     * @var array
     */
    public $comments = [];

    /**
     * A collection of materialized Meerkat threads.
     *
     * @var array
     */
    public $threads = [];

    /**
     * The total number of comments.
     *
     * @var int
     */
    public $commentCount = 0;

    /**
     * The total number of unique threads.
     *
     * @var int
     */
    public $threadCount = 0;

}
