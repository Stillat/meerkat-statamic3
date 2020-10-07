<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Data\ValueWrapper;
use Stillat\Meerkat\Core\Exceptions\ParserException;
use Stillat\Meerkat\Core\Parsing\ArrayParser;

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
     * @param string $values The input values.
     * @return array
     * @throws ParserException
     */
    public function parseParameterValues($values)
    {
        $returnValues = [];

        $parserParts = ArrayParser::getValues($values, ',');
        $parsedValues = array_map('trim', $parserParts);

        foreach ($parsedValues as $value) {
            $returnValues[] = ValueWrapper::unwrap($value);
        }

        return $returnValues;
    }

}
