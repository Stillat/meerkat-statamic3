<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Retrievers\SearchTermCommentIdRetriever;
use Stillat\Meerkat\Core\Search\EngineFactory;

/**
 * Class Search
 *
 * Registers the `search:terms` filter, which allows arbitrary full-text searching.
 *
 * search:terms(hello, there)
 *
 * @since 2.0.0
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 *
 * @see CommentFilter
 */
class Search
{
    const FILTER_SEARCH_TERMS = 'search:terms';

    const PARAM_TERMS = 'terms';

    public function register(CommentFilterManager $manager)
    {
        $manager->filterWithTagContext(Search::FILTER_SEARCH_TERMS, function ($comments) {
            $terms = $this->get(Search::PARAM_TERMS, '');

            if (EngineFactory::hasInstance() === false) {
                return $comments;
            }

            $commentIds = SearchTermCommentIdRetriever::getIdsFromSearchTerms($comments, $terms);

            return array_filter($comments, function (CommentContract $comment) use ($commentIds) {
                return in_array($comment->getId(), $commentIds);
            });
        }, Search::PARAM_TERMS);
    }
}
