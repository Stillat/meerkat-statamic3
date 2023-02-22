<?php

namespace Stillat\Meerkat\Core\Authoring;

/**
 * Class InitialsGenerator
 *
 * Contains utilities for parsing strings into an initialism.
 *
 * @since 2.0.0
 */
class InitialsGenerator
{
    /**
     * Parses the input string to create a human-friendly initialism.
     *
     * @param  string  $name The input string to create an initialism from.
     * @param  bool  $onlyCapitals Indicates if the method should only consider UPPER-cased characters.
     * @param  string  $separator The separator to use when deconstructing the input string.
     * @return string
     */
    public static function getInitials($name, $onlyCapitals = false, $separator = ' ')
    {
        $initials = '';
        $token = strtok($name, $separator);

        while ($token !== false) {
            $character = mb_substr($token, 0, 1);

            if ($onlyCapitals && mb_strtoupper($character) !== $character) {
                $token = strtok($separator);

                continue;
            }

            $initials .= $character;
            $token = strtok($separator);
        }

        return $initials;
    }
}
