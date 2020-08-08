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
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 * @since 1.5.85
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class UserFromAuth
{

    /**
     * Registers the default Meerkat users:<> filters.
     *
     * @param CommentFilterManager $manager The filter manager.
     */
    public function register(CommentFilterManager $manager)
    {
        $manager->filter('user:from_auth', function ($comments) {
            $includeUsers = TypeConversions::getBooleanValue($this->get(IsFilters::PARAM_COMPARISON, false));

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