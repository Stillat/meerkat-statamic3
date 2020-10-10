<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\PropertyRedirector;
use Stillat\Meerkat\Core\Support\TypeConversions;
/**
 * Class WhereNotIn
 *
 * Registers the not:where:in filter, the logical opposite of where:in.
 *
 * not:where:in(property_name, a, list, of, values)
 *
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class WhereNotIn
{

    const FILTER_WHERE_NOT_IN = 'not:where:in';

    public function register(CommentFilterManager $manager)
    {
        $manager->filter(WhereNotIn::FILTER_WHERE_NOT_IN, function ($comments) {
            $propertyToCheck = PropertyRedirector::redirect($this->get('property', null));
            $values = TypeConversions::parseToArray($this->get('values', []));

            return array_filter($comments, function (CommentContract $comment) use ($propertyToCheck, $values) {
                $commentValue = $comment->getDataAttribute($propertyToCheck);

                return in_array($commentValue, $values) === false;
            });
        }, 'property, comparison, value');
    }

}
