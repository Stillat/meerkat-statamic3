<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\ParserException;

/**
 * Class FilterRunner
 *
 * Provides utilities to make it easier to execute Meerkat comment filters within context.
 *
 * @package Stillat\Meerkat\Core\Data\Filters
 * @since 2.0.0
 */
class FilterRunner
{

    /**
     * The CommentFilterManager instance.
     *
     * @var CommentFilterManager
     */
    protected $filterManager = null;

    /**
     * The IdentityManagerContract implementation instance.
     *
     * @var IdentityManagerContract
     */
    protected $identityManager = null;

    public function __construct(CommentFilterManager $filterManager, IdentityManagerContract $identityManager)
    {
        $this->filterManager = $filterManager;
        $this->identityManager = $identityManager;
    }

    /**
     * Returns access to the CommentFilterManager.
     *
     * @return CommentFilterManager|null
     */
    public function getFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * Processes the provided filters within the surrounding context.
     *
     * @param CommentContract[] $comments The comments to filter.
     * @param array $params The run-time parameters, if any.
     * @param string $filters The Meerkat filters string.
     * @param mixed|null $context The Meerkat run-time context, if any.
     * @param string $tagContext The run-time templating context, if any.
     * @return array
     * @throws FilterException
     * @throws ParserException
     */
    public function processFilters($comments, $params, $filters, $context = null, $tagContext = '')
    {
        $filters = $this->filterManager->getFilterMap($filters);
        $filters = explode('|', $filters);

        $currentIdentity = $this->identityManager->getIdentityContext();

        if ($currentIdentity !== null) {
            $this->filterManager->setUser($currentIdentity);
        }

        $themeFilterComments = $comments;

        $commentIdsToKeep = [];

        foreach ($filters as $filter) {
            if ($this->filterManager->hasFilter(trim($filter))) {
                $filterResults = $this->filterManager->runFilter(
                    $filter,
                    $themeFilterComments,
                    $params,
                    $context,
                    $tagContext
                );

                if ($filterResults !== null && is_array($filterResults)) {
                    $commentIdsToKeep = [];

                    /** @var CommentContract $comment */
                    foreach ($filterResults as $comment) {
                        $commentIdsToKeep[] = $comment->getId();
                    }

                    $themeFilterComments = array_filter($themeFilterComments, function ($comment) use ($commentIdsToKeep) {
                        $thisId = $comment->getId();

                        return in_array($thisId, $commentIdsToKeep);
                    });
                }
            } else {
                throw new FilterException($filter . ' Meerkat Filter could not be found.');
            }
        }

        return $this->recursivelyFilterComments($comments, $commentIdsToKeep);
    }

    /**
     * Filters list of comments, as well as all nested replies.
     *
     * @param CommentContract[] $comments The comment.
     * @param string[] $idsToKeep The comment identifiers to maintain.
     * @return CommentContract[]
     */
    private function recursivelyFilterComments($comments, $idsToKeep)
    {
        return array_filter($comments, function ($comment) use ($idsToKeep) {
            $shouldKeep = in_array($comment->getId(), $idsToKeep);

            if ($shouldKeep && $comment->isParent()) {
                $filteredReplies = $this->recursivelyFilterComments($comment->getReplies(), $idsToKeep);
                $comment->setReplies($filteredReplies);
                $comment->setDataAttribute(CommentContract::KEY_CHILDREN, $comment->getReplies());

                if (count($filteredReplies) === 0) {
                    // Rewrite some values.
                    // We will leave the descendents nodes alone, though.
                    $comment->setDataAttribute(CommentContract::KEY_HAS_REPLIES, false);
                }
            }

            return $shouldKeep;
        });
    }

}
