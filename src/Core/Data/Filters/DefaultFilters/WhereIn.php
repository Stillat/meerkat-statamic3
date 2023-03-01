<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\PropertyRedirector;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class WhereIn
 *
 * Registers the where:in filters.
 *
 * where:in(property_name, a, list, of, values)
 *
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 *
 * @see CommentFilter
 */
class WhereIn
{
    const FILTER_WHERE_IN = 'where:in';

    public function register(CommentFilterManager $manager)
    {
        $manager->filter(WhereIn::FILTER_WHERE_IN, function ($comments) {
            $propertyToCheck = PropertyRedirector::redirect($this->get('property', null));
            $values = TypeConversions::parseToArray($this->get('values', []));

            return array_filter($comments, function (CommentContract $comment) use ($propertyToCheck, $values) {
                $commentValue = $comment->getDataAttribute($propertyToCheck);

                return in_array($commentValue, $values);
            });
        }, 'property, values');
    }
}
