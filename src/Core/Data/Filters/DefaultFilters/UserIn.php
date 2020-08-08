<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Helpers\UserHelpers;
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
class UserIn
{
    const PARAM_USERS = 'users';

    /**
     * Registers the default user:<> Meerkat filters.
     *
     * @param CommentFilterManager $manager The filter manager.
     */
    public function register(CommentFilterManager $manager)
    {
        $manager->filter('user:in', function ($comments) {
            $tempUsers = TypeConversions::parseToArray($this->get(UserIn::PARAM_USERS, []));
            $userList = UserHelpers::buildContextualUserList($tempUsers);

            return array_filter($comments, function (CommentContract $comment) use ($userList) {
                if ($comment->leftByAuthenticatedUser() && $comment->getAuthor() !== null) {
                    return in_array($comment->getAuthor()->getId(), $userList);
                }

                return false;
            });
        }, UserIn::PARAM_USERS);

        $manager->filter('user:not_in', function ($comments) {
            $tempUsers = TypeConversions::parseToArray($this->get(UserIn::PARAM_USERS, []));
            $userList = UserHelpers::buildContextualUserList($tempUsers);

            return array_filter($comments, function (CommentContract $comment) use ($userList) {
                if ($comment->leftByAuthenticatedUser() && $comment->getAuthor() !== null) {
                    return in_array($comment->getAuthor()->getId(), $userList) == false;
                }

                return true;
            });
        }, UserIn::PARAM_USERS);
    }

}