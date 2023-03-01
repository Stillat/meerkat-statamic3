<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\PropertyRedirector;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class Like
 *
 * Registers the "not:like" filter, anSQL-style negated LIKE filter.
 *
 * not:like(content_raw, %some-value%)
 *
 * @since 2.0.0
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 *
 * @see CommentFilter
 */
class NotLike
{
    const FILTER_NOT_LIKE = 'not:like';

    public function register(CommentFilterManager $manager)
    {
        $manager->filter(NotLike::FILTER_NOT_LIKE, function ($comments) {
            $propertyToCheck = PropertyRedirector::redirect($this->get('property', null));
            $pattern = $this->get('pattern');

            if (Str::isNullOrEmpty($propertyToCheck)) {
                throw new FilterException('`not:like` filter: $property does not accept `null` values.');
            }

            return array_filter($comments, function (CommentContract $comment) use ($propertyToCheck, $pattern) {
                $commentValue = $comment->getDataAttribute($propertyToCheck);

                return Str::isLike($pattern, $commentValue) === false;
            });
        }, 'property, pattern');
    }
}
