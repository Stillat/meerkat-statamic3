<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Exceptions\ComparisonException;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class Comparator
 *
 * Provides utilities for comparing values using a string-input comparison operator.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class Comparator
{
    const COMPARISON_LOSE_EQUALITY = '=';
    const COMPARISON_LOSE_INEQUALITY = '!=';
    const COMPARISON_STRICT_EQUALITY = '==';
    const COMPARISON_STRICT_INEQUALITY = '!==';
    const COMPARISON_GREATER_THAN = '>';
    const COMPARISON_GREATER_THAN_OR_EQUALS = '>=';
    const COMPARISON_LESS_THAN = '<';
    const COMPARISON_LESS_THAN_OR_EQUALS = '<=';

    /**
     * A list of all comparisons compatible with the comparator.
     *
     * @var string[]
     */
    protected $comparisons = [
        self::COMPARISON_LOSE_EQUALITY,
        self::COMPARISON_STRICT_INEQUALITY,
        self::COMPARISON_LOSE_EQUALITY,
        self::COMPARISON_STRICT_EQUALITY,
        self::COMPARISON_GREATER_THAN,
        self::COMPARISON_GREATER_THAN_OR_EQUALS,
        self::COMPARISON_LESS_THAN,
        self::COMPARISON_LESS_THAN_OR_EQUALS
    ];

    public function compare($property, $comparison, $checkValue)
    {
        $comparison = Str::withoutSpaces($comparison);

        if (in_array($comparison, $this->comparisons) === false) {
            throw new ComparisonException("${comparison} is not a valid comparison operator.");
        }

        if ($comparison === self::COMPARISON_STRICT_INEQUALITY) {
            return $this->checkStrictInequality($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_LOSE_INEQUALITY) {
            return $this->checkLoseInequality($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_STRICT_EQUALITY) {
            return $this->checkStrictEquality($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_GREATER_THAN) {
            return $this->checkGreaterThan($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_GREATER_THAN_OR_EQUALS) {
            return $this->checkGreaterOrEqualTo($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_LESS_THAN) {
            return $this->checkLessThan($property, $checkValue);
        } elseif ($comparison === self::COMPARISON_LESS_THAN_OR_EQUALS) {
            return $this->checkLessThanOrEqualTo($property, $checkValue);
        }

        return $this->checkEquality($property, $checkValue);
    }

    /**
     * Tests if the provided value is strictly not equal to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkStrictInequality($property, $checkValue)
    {
        return $property !== $checkValue;
    }

    /**
     * Tests if the provided value is not equal to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkLoseInequality($property, $checkValue)
    {
        return $property != $checkValue;
    }

    /**
     * Tests is the provided value is equal to and has the same type as the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkStrictEquality($property, $checkValue)
    {
        return $property === $checkValue;
    }

    /**
     * Tests is the provided value is greater than the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkGreaterThan($property, $checkValue)
    {
        return $property > $checkValue;
    }

    /**
     * Tests is the provided value is greater than or equal to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkGreaterOrEqualTo($property, $checkValue)
    {
        return $property >= $checkValue;
    }

    /**
     * Tests is the provided value is less than to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkLessThan($property, $checkValue)
    {
        return $property < $checkValue;
    }

    /**
     * Tests is the provided value is less than or equal to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkLessThanOrEqualTo($property, $checkValue)
    {
        return $property <= $checkValue;
    }

    /**
     * Tests is the provided value is equal to the check value.
     *
     * @param mixed $property The value to check.
     * @param mixed $checkValue The value to check against.
     * @return bool
     */
    protected function checkEquality($property, $checkValue)
    {
        return $property == $checkValue;
    }

}
