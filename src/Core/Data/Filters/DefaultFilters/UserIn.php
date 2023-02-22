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
 * user:in(list, of, ids)
 * user:not_in(list, of, ids) - legacy alias
 * not:user:in(list, of, ids)
 *
 * @since 1.5.85
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 *
 * @see CommentFilter
 */
class UserIn
{
    const FILTER_USER_IN = 'user:in';

    const FILTER_USER_NOT_IN = 'user:not_in';

    const FILTER_USER_NOT_IN_ALIAS = 'not:user:in';

    const PARAM_USERS = 'users';

    /**
     * Registers the default user:<> Meerkat filters.
     *
     * @param  CommentFilterManager  $manager The filter manager.
     */
    public function register(CommentFilterManager $manager)
    {
        $manager->filter(UserIn::FILTER_USER_IN, function ($comments) {
            $tempUsers = TypeConversions::parseToArray($this->get(UserIn::PARAM_USERS, []));
            $userList = UserHelpers::buildContextualUserList($tempUsers);

            return array_filter($comments, function (CommentContract $comment) use ($userList) {
                if ($comment->leftByAuthenticatedUser() && $comment->getAuthor() !== null) {
                    return in_array($comment->getAuthor()->getId(), $userList);
                }

                return false;
            });
        }, UserIn::PARAM_USERS);

        $manager->filter(UserIn::FILTER_USER_NOT_IN, function ($comments) {
            $tempUsers = TypeConversions::parseToArray($this->get(UserIn::PARAM_USERS, []));
            $userList = UserHelpers::buildContextualUserList($tempUsers);

            return array_filter($comments, function (CommentContract $comment) use ($userList) {
                if ($comment->leftByAuthenticatedUser() && $comment->getAuthor() !== null) {
                    return in_array($comment->getAuthor()->getId(), $userList) == false;
                }

                return true;
            });
        }, UserIn::PARAM_USERS);

        $manager->filter(UserIn::FILTER_USER_NOT_IN_ALIAS, function ($comments) {
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
