<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Comparator;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class Where
 *
 * Registers the where filter, allowing arbitrary property comparisons.
 *
 * where(property_name, =, some_value) - lose equality
 * where(property_name, !=, some_value) - lose inequality
 * where(property_name, ==, some_value) - strict equality
 * where(property_name, !==, some_value) - strict inequality
 * where(property_name, >, some_value) - greater than
 * where(property_name, >=, some_value) - greater than or equal to
 * where(property_name, <, some_value) - less than
 * where(property_name, <=, some_value) - less than or equal to
 *
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class Where
{

    const FILTER_WHERE = 'where';

    public function register(CommentFilterManager $manager)
    {
        $manager->filter(Where::FILTER_WHERE, function ($comments) {
            $propertyToCheck = $this->get('property', null);
            $comparison = $this->get('comparison', null);
            $value = $this->get('value');

            if (Str::isNullOrEmpty($propertyToCheck)) {
                throw new FilterException('`where` filter: $property does not accept `null` values.');
            }

            if (Str::isNullOrEmpty($comparison)) {
                throw new FilterException('`where` filter: $comparison does not accept `null` values.');
            }

            $comparator = new Comparator();

            return array_filter($comments, function (CommentContract $comment) use ($propertyToCheck, $comparison, $value, $comparator) {
                $commentValue = $comment->getDataAttribute($propertyToCheck);

                return $comparator->compare($commentValue, $comparison, $value);
            });
        }, 'property, comparison, value');
    }

}
