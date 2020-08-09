<?php

namespace Stillat\Meerkat\Core\Search\Providers;

use Stillat\Meerkat\Core\Contracts\Search\ProvidesSearchableAttributesContract;
use Stillat\Meerkat\Core\Contracts\Search\SearchAlgorithmContract;

/**
 * Class BitapSearchProvider
 *
 * Implements the Bitap search algorithm for searching text.
 *
 * @package Stillat\Meerkat\Core\Search\Providers
 * @since 2.0.0
 */
class BitapSearchProvider implements SearchAlgorithmContract
{

    /**
     * Searches the provided text with the given pattern.
     *
     * @param string|ProvidesSearchableAttributesContract $text The text to search.
     * @param string $pattern The search pattern.
     * @return int
     */
    public function search($text, $pattern)
    {
        if (is_array($text)) {
            return -1;
        }

        if (is_object($text)) {
            return -1;
        }

        if (is_string($text) === false) {
            $text = (string)$text;
        }

        $text = mb_strtolower($text);
        $pattern = mb_strtolower($pattern);

        $patternLen = mb_strlen($pattern);
        $textLen = mb_strlen($text);

        if ($textLen == 0) {
            return -1;
        }

        if (empty($pattern)) {
            return 0;
        }

        if ($patternLen > 31) {
            return -1;
        }

        $patternMask = [];
        $r = ~1;

        for ($i = 0; $i <= 127; ++$i) {
            $patternMask[$i] = ~0;
        }

        for ($i = 0; $i < $patternLen; ++$i) {
            $patternMask[ord($pattern[$i])] &= ~(1 << $i);
        }

        for ($i = 0; $i < $textLen; ++$i) {
            $r |= $patternMask[ord($text[$i])];
            $r <<= 1;

            if (0 == ($r & (1 << $patternLen))) {
                return ($i - $patternLen) + 1;
            }
        }

        return -1;
    }

}
