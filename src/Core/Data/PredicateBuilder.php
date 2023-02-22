<?php

namespace Stillat\Meerkat\Core\Data;

use Closure;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;

/**
 * Class PredicateBuilder
 *
 * Provides utilities for generating dynamic sorting predicates.
 *
 * @since 2.0.0
 */
class PredicateBuilder
{
    const KEY_PROPERTY = 'property';

    const KEY_IS_ASC = 'asc';

    const KEY_DIRECTION_ASC = 'asc';

    const KEY_DIRECTION_DESC = 'desc';

    /**
     * A collection of comparisons to apply to a data collection.
     *
     * @var array
     */
    protected $comparisons = [];

    /**
     * Returns a sort string representing the current sort configuration.
     *
     *
     * @return string
     */
    public function getSortString()
    {
        $sortStringParts = [];

        foreach ($this->comparisons as $comparison) {
            if (! is_array($comparison) || array_key_exists(self::KEY_IS_ASC, $comparison) === false ||
                array_key_exists(self::KEY_PROPERTY, $comparison) === false) {
                continue;
            }

            $direction = self::KEY_DIRECTION_DESC;
            $isAsc = $comparison[self::KEY_IS_ASC];

            if ($isAsc) {
                $direction = self::KEY_DIRECTION_ASC;
            }

            $sortStringParts[] = $comparison[self::KEY_PROPERTY].','.$direction;
        }

        return implode('|', $sortStringParts);
    }

    /**
     * Sorts the collection using the provided property in ascending order.
     *
     * This method resets the internal comparisons.
     *
     * @param  string  $p The property name.
     * @return $this
     */
    public function asc($p)
    {
        // Reset the comparisons.
        $this->comparisons = [];

        return $this->thenByAsc($p);
    }

    /**
     * Sorts the collection using the provided property in ascending order.
     *
     * @param  string  $p The property name.
     * @return $this
     */
    public function thenByAsc($p)
    {
        $property = null;

        if (is_callable($p)) {
            $property = $p();
        } elseif (is_string($p)) {
            $property = $p;
        }

        if ($property !== null) {
            $this->addComparison($property, true);
        }

        return $this;
    }

    /**
     * Adds a comparison to the internal sorting comparisons.
     *
     * @param  string  $propertyName The property name.
     * @param  bool  $asc Indicates if the sort order is ascending, else descending.
     */
    private function addComparison($propertyName, $asc)
    {
        $this->comparisons[] = [
            self::KEY_PROPERTY => $propertyName,
            self::KEY_IS_ASC => $asc,
        ];
    }

    /**
     * Sorts the collection using the provided property in descending order.
     *
     * This method resets the internal comparisons.
     *
     * @param  string  $p The property name.
     * @return $this
     */
    public function desc($p)
    {
        // Reset the comparisons.
        $this->comparisons = [];

        return $this->thenByDesc($p);
    }

    /**
     * Sorts the collection using the provided property in descending order.
     *
     * @param  string  $p The property name.
     * @return $this
     */
    public function thenByDesc($p)
    {
        $property = null;

        if (is_callable($p)) {
            $property = $p();
        } elseif (is_string($p)) {
            $property = $p;
        }

        if ($property !== null) {
            $this->addComparison($property, false);
        }

        return $this;
    }

    /**
     * Sorts the provided data using previously defined sort predicates.
     *
     * @param  array  $data The data to sort.
     * @return array
     */
    public function sort($data)
    {
        $sortFunc = $this->buildPredicate();

        uasort($data, $sortFunc);

        foreach ($data as $el) {
            if ($el instanceof Comment) {
                $replies = $el->getReplies();

                if (count($replies) > 0) {
                    uasort($replies, $sortFunc);

                    $replies = array_values($replies);

                    $el->setDataAttribute(CommentContract::KEY_CHILDREN, $replies);
                    $el->setReplies($replies);
                }
            }
        }

        return $data;
    }

    /**
     * Builds the sort comparison function.
     *
     * @return Closure
     */
    private function buildPredicate()
    {
        return function ($a, $b) {
            $mapA = [];
            $mapB = [];

            foreach ($this->comparisons as $comparison) {
                $prop = $comparison[self::KEY_PROPERTY];

                if (is_object($a) && is_object($b)) {
                    if ($a instanceof DataObjectContract &&
                        $b instanceof DataObjectContract) {
                        if ($comparison[self::KEY_IS_ASC] === true) {
                            $mapA[] = $a->getDataAttribute($prop);
                            $mapB[] = $b->getDataAttribute($prop);
                        } else {
                            $mapA[] = $b->getDataAttribute($prop);
                            $mapB[] = $a->getDataAttribute($prop);
                        }
                    } else {
                        if ($comparison[self::KEY_IS_ASC] === true) {
                            $mapA[] = $a->$prop;
                            $mapB[] = $b->$prop;
                        } else {
                            $mapA[] = $b->$prop;
                            $mapB[] = $a->$prop;
                        }
                    }
                } elseif (is_array($a)) {
                    if ($comparison[self::KEY_IS_ASC] === true) {
                        $mapA[] = $a[$prop];
                        $mapB[] = $b[$prop];
                    } else {
                        $mapA[] = $b[$prop];
                        $mapB[] = $a[$prop];
                    }
                }
            }

            if ($mapA == $mapB) {
                return 0;
            }

            if ($mapA < $mapB) {
                return -1;
            }

            return 1;
        };
    }
}
