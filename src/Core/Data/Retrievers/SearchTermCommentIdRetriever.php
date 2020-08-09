<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Search\EngineFactory;

/**
 * Class SearchTermCommentIdRetriever
 *
 * Provides helpers and utilities from retrieving comment identifiers from search results.
 *
 * @package Stillat\Meerkat\Core\Data\Retrievers
 * @since 2.0.0
 */
class SearchTermCommentIdRetriever
{

    /**
     * Attempts to locate comment identifiers from a collection of search results.
     *
     * @param CommentContract[] $comments The comments to search.
     * @param string $searchTerms The search terms.
     * @return string[]
     */
    public static function getIdsFromSearchTerms($comments, $searchTerms)
    {
        if (EngineFactory::hasInstance() === false) {
            return [];
        }

        $searchResults = EngineFactory::$searchEngine->search($searchTerms, $comments);

        return CommentIdRetriever::getCommentIds($searchResults);
    }

}
