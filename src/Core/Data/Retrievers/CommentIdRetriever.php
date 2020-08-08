<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class CommentIdRetriever
 *
 * Provides utilities for getting unique comment identifiers from a comment collection.
 *
 * @package Stillat\Meerkat\Core\Data\Retrievers
 * @since 2.0.0
 */
class CommentIdRetriever
{

    /**
     * Gets the unique comment identifiers from the data.
     *
     * @param CommentContract[] $data The data to analyze.
     * @return string[]
     */
    public static function getCommentIds($data)
    {
        $commentIds = [];

        foreach ($data as $comment) {
            $commentIds[] = $comment->getId();
        }

        return array_unique($commentIds);
    }

}
