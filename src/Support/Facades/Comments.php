<?php

namespace Stillat\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;

/**
 * Class Comments
 *
 * Provides a static API for the CommentFilterManager instance.
 *
 * @since 2.0.0
 *
 * @method static string getFilterMap($filter)
 * @method static void filter($filterName, $callback, $params = '')
 * @method static void filterGroup($groupName, $filters);
 * @method static void filterWithTagContext($filterName, $callback, $params = '', $supportedTags= [])
 * @method static void resolve($variableName, $callback);
 * @method static void restrictFilter($filterName, $tagContexts)
 * @method static void removeRestrictions($filterName)
 * @method static mixed|null runFilter($filterName, $comments, $parameters, $context = null, $tagContext = '')
 * @method static bool hasFilter($filterName)
 * @method static mixed|null getUser()
 * @method static void setUser(AuthorContract $identity)
 *
 * @see \Stillat\Meerkat\Core\Data\\CommentFilterManager
 */
class Comments extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CommentFilterManager::class;
    }
}
