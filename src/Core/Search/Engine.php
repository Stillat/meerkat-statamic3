<?php

namespace Stillat\Meerkat\Core\Search;

use Stillat\Meerkat\Core\Contracts\Search\ProvidesSearchableAttributesContract;
use Stillat\Meerkat\Core\Contracts\Search\SearchAlgorithmContract;

/**
 * Class Engine
 *
 * Provides search mechanisms for filtering Meerkat datasets.
 *
 * @package Stillat\Meerkat\Core\Search
 * @since 2.0.0
 */
class Engine
{

    /**
     * The search algorithm implementation instance.
     *
     * @var SearchAlgorithmContract|null
     */
    protected $searchAlgorithm = null;

    /**
     * The data attributes to include when searching a data-set.
     *
     * @var string[]
     */
    private $searchAttributes = [];

    public function __construct(SearchAlgorithmContract $searchAlgorithm)
    {
        $this->searchAlgorithm = $searchAlgorithm;
    }

    /**
     * Sets the search attributes to search for.
     * @param string[] $attributes The attributes to search for in the data-set.
     */
    public function setSearchAttributes($attributes)
    {
        if (is_array($attributes)) {
            $this->searchAttributes = $attributes;
        }
    }

    /**
     * Searches the provided dataset against the defined search attributes and terms.
     *
     * @param $searchTerms
     * @param $dataset
     * @return array
     */
    public function search($searchTerms, $dataset)
    {
        $itemsToReturn = [];

        foreach ($dataset as $itemKey => $item) {
            $searchAttributes = $this->searchAttributes;

            if ($item instanceof ProvidesSearchableAttributesContract) {
                $searchAttributes = $item->getSearchableAttributes();
            }

            foreach ($searchAttributes as $attribute) {
                $valueToSearch = null;

                if (is_array($item) && array_key_exists($attribute, $item)) {
                    $valueToSearch = $item[$attribute];
                } else if (is_object($item)) {
                    if (method_exists($item, 'getDataAttribute')) {
                        $valueToSearch = $item->getDataAttribute($attribute, null);
                    }
                }

                if ($this->searchAlgorithm->search($valueToSearch, $searchTerms) >= 0) {
                    $itemsToReturn[] = $item;
                }
            }
        }

        return $itemsToReturn;
    }

}