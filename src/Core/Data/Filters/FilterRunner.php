<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Exceptions\FilterException;

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
     * Processes the provided filters within the surrounding context.
     *
     * @param CommentContract[] $comments The comments to filter.
     * @param array $params The run-time parameters, if any.
     * @param string $filters The Meerkat filters string.
     * @param mixed|null $context The Meerkat run-time context, if any.
     * @param string $tagContext The run-time templating context, if any.
     * @return array
     * @throws FilterException
     */
    public function processFilters($comments, $params, $filters, $context = null, $tagContext = '')
    {
        $filters = $this->filterManager->getFilterMap($filters);
        $filters = explode('|', $filters);

        $currentIdentity = $this->identityManager->getIdentityContext();

        $this->filterManager->setUser($currentIdentity);
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

        // Re-create the main data array.
        return array_filter($comments, function ($comment) use ($commentIdsToKeep) {
            return in_array($comment->getId(), $commentIdsToKeep);
        });
    }

}
