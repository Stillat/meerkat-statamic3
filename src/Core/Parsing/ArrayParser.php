<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Exceptions\ParserException;

/**
 * Class ArrayParser
 *
 * Provides utilities for constructing an array from an input string.
 *
 * Input String: "'hello', 'this is a, nested, \'string\'', 'value'"
 * Results in the following array element values:
 *  0: hello
 *  1: this is a, nested, 'string'
 *  2: value
 *
 * The input string "hello, 'this is a, nested, \'string\', value" is equivalent.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
class ArrayParser
{

    const TOKEN_SEPARATOR = ',';
    const TOKEN_ESCAPE = '\\';

    const TOKEN_QUOTE_DELIMITER = '\'';

    /**
     * Parses an input string into an array of values.
     *
     * @param string $inputString The input string.
     * @param string $delimiter The element delimiter.
     * @return array
     * @throws ParserException
     */
    public static function getValues($inputString, $delimiter = ',')
    {
        if (is_array($inputString)) {
            return $inputString;
        }

        $valueParts = str_split($inputString);
        $parserParts = [];

        $charBuffer = [];

        $lastBufferItem = count($valueParts) - 1;
        $inQuotes = false;

        for ($i = 0; $i < count($valueParts); $i += 1) {
            $thisChar = $valueParts[$i];
            $peek = null;

            if (array_key_exists($i + 1, $valueParts)) {
                $peek = $valueParts[$i + 1];
            }

            if ($i === $lastBufferItem) {
                if ($inQuotes === true && $thisChar === self::TOKEN_QUOTE_DELIMITER) {
                    $parserParts[] = implode('', $charBuffer);
                    continue;
                } elseif ($inQuotes === true && $thisChar !== self::TOKEN_QUOTE_DELIMITER) {
                    throw new ParserException('Unmatched quotes at end of sequence: '.$inputString);
                } else {
                    $charBuffer[] = $thisChar;
                    $parserParts[] = implode('', $charBuffer);
                }
            } elseif ($thisChar === self::TOKEN_ESCAPE) {
                if ($peek === null) {
                    throw new ParserException('Unrecognized escape sequence: \\null');
                }


                if ($peek === self::TOKEN_QUOTE_DELIMITER) {
                    $charBuffer[] = self::TOKEN_QUOTE_DELIMITER;
                    $i += 2;

                    $inQuotes = true;
                    continue;
                } elseif ($peek === self::TOKEN_ESCAPE) {
                    $charBuffer[] = self::TOKEN_ESCAPE;
                    $i += 1;
                }

            } elseif ($thisChar === self::TOKEN_QUOTE_DELIMITER) {
                if ($inQuotes === false) {
                    $inQuotes = true;
                    continue;
                }

                $inQuotes = false;
                continue;
            } elseif ($thisChar === $delimiter) {
                if ($inQuotes === true) {
                    $charBuffer[] = $thisChar;
                    continue;
                }
                $parserParts[] = implode('', $charBuffer);
                $charBuffer = [];
            } else {
                $charBuffer[] = $thisChar;
            }
        }

        $parserParts = array_map('trim', $parserParts);

        return $parserParts;
    }

}