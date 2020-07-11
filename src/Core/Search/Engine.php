<?php

namespace Stillat\Meerkat\Core\Search;

/**
 * Provides search mechanisms for filtering Meerkat datasets.
 *
 * @package Stillat\Meerkat\Core\Search
 * @since 2.0.0
 */
class Engine
{

    /**
     * The data attributes to include when searching a data-set.
     * @var string[]
     */
    private $searchAttributes = [];

    /**
     * Sets the search attributes to search for.
     * @param string[] $attributes The attributes to search for in the data-set.
     */
    public function setSearchAttributes($attributes) {
        if (is_array($attributes)) {
            $this->searchAttributes = $attributes;
        }
    }

    /**
     * Crude search implementation.
     *
     * TODO: REFACTOR TO BE BETTER.
     *
     * @param $needles
     * @param $haystack
     * @return bool
     */
    protected  function containsMatch($needles, $haystack) {
        foreach (explode(' ', $needles) as $needle) {
            if (stristr($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Searches the provided dataset against the defined search attributes and terms.
     *
     * @param $searchTerms
     * @param $dataset
     * @return array
     */
    public function search($searchTerms, $dataset) {
        $itemsToReturn = [];

        foreach ($dataset as $itemKey => $item) {
            foreach ($this->searchAttributes as $attribute) {
                $valueToSearch = null;

                if (is_array($item) && array_key_exists($attribute, $item)) {
                    $valueToSearch = $item[$attribute];
                } else if (is_object($item)) {
                    if (method_exists($item, 'getDataAttribute')) {
                        $valueToSearch = $item->getDataAttribute($attribute, null);
                    }
                }

                if ($valueToSearch === null) {
                    continue;
                }

                if ($this->containsMatch($searchTerms, $valueToSearch)) {
                    $itemsToReturn[$itemKey] = $item;
                    break;
                }
            }
        }

        return $itemsToReturn;
    }

}