<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class UserFromAuth
 *
 * Contains the user:<> default filters.
 *
 * user:from_auth(true) - Returns only comments from authenticated users.
 * user:from_auth(false) - Returns only comments from un-authenticated users.
 *
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 * @since 1.5.85
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class UserFromAuth
{

    const FILTER_FROM_AUTHENTICATED_USER = 'user:from_auth';

    /**
     * Registers the default Meerkat users:<> filters.
     *
     * @param CommentFilterManager $manager The filter manager.
     */
    public function register(CommentFilterManager $manager)
    {
        $manager->filter(UserFromAuth::FILTER_FROM_AUTHENTICATED_USER, function ($comments) {
            $comparisonFilter = $this->get(IsFilters::PARAM_COMPARISON, false);

            if ($comparisonFilter === IsFilters::FILTER_ANY) {
                return $comments;
            }

            $includeUsers = TypeConversions::getBooleanValue($comparisonFilter);

            return array_filter($comments, function (CommentContract $comment) use ($includeUsers) {
                $hasUser = $comment->leftByAuthenticatedUser();

                if ($includeUsers) {
                    if ($hasUser) {
                        return true;
                    } else {
                        return false;
                    }
                }

                if ($hasUser) {
                    return false;
                }

                return true;
            });
        }, IsFilters::PARAM_COMPARISON);
    }

}
