<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Data\ValueWrapper;

/**
 * Class ParameterParser
 *
 * Parses filter parameter names and values.
 *
 * @package Stillat\Meerkat\Core\Data\Filters
 * @since 2.0.0
 */
class ParameterParser
{

    /**
     * Parses the parameter names from an input string.
     *
     * @param string $parameters The parameters.
     * @return string[]
     */
    public function parseParameterString($parameters)
    {
        return array_map('trim', explode(',', $parameters));
    }

    /**
     * Parses the individual values from the filter's input stream.
     *
     * @param stringn $values The input values.
     * @return array
     */
    public function parseParameterValues($values)
    {
        $returnValues = [];
        $parsedValues = [];
        $split = str_split($values);

        $currentPieces = [];

        $last = count($split) - 1;
        for ($i = 0; $i < count($split); $i++) {
            $thisToken = $split[$i];

            if ($thisToken === ',') {
                $parsedValues[] = implode('', $currentPieces);
                $currentPieces = [];
            } elseif ($i === $last) {
                $currentPieces[] = $thisToken;
                $parsedValues[] = implode('', $currentPieces);

                break;
            } else {
                $currentPieces[] = $thisToken;
            }
        }

        $parsedValues = array_map('trim', $parsedValues);

        foreach ($parsedValues as $value) {
            $returnValues[] = ValueWrapper::unwrap($value);
        }

        return $returnValues;
    }

}
