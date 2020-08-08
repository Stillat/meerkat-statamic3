<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class CommentThreadIdRetriever
 *
 * Provides utilities for getting unique thread identifiers from a comment collection.
 *
 * @package Stillat\Meerkat\Core\Data\Retrievers
 * @since 2.0.0
 */
class CommentThreadIdRetriever
{

    /**
     * Gets a unique list of thread identifiers from the data collection.
     *
     * @param CommentContract[] $data The data to analyze.
     * @return string[]
     */
    public static function getThreadIds($data)
    {
        $threadIds = [];

        foreach ($data as $comment) {
            $threadIds[] = $comment->getThreadId();
        }

        return array_unique($threadIds);
    }

}
