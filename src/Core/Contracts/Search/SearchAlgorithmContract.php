<?php

namespace Stillat\Meerkat\Core\Contracts\Search;

/**
 * Interface SearchAlgorithmContract
 *
 * Provides a consistent API for text-based search algorithms.
 *
 * @package Stillat\Meerkat\Core\Contracts\Search
 * @since 2.0.0
 */
interface SearchAlgorithmContract
{

    /**
     * Searches the provided text with the given pattern.
     *
     * @param string $text The text to search.
     * @param string $pattern The search pattern.
     * @return int
     */
    public function search($text, $pattern);

}
