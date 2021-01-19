<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\FilterParserException;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class ExpressionParser
 *
 * Parsers Query filter input expressions.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.1.21
 */
class ExpressionParser
{
    const TOKEN_FILTER_DELIMITER = '|';
    const TOKEN_INPUT_START = '(';
    const TOKEN_INPUT_END = ')';
    const TOKEN_INPUT_DELIMITER = ',';
    const TOKEN_STR_DELIMITER = '\'';
    const TOKEN_STR_ESCAPE = '\\';
    const TOKEN_GROUP_START = '@';

    const KEY_NAME = 'name';
    const KEY_INPUT = 'input';
    const KEY_VALUE = 'value';
    const KEY_TYPE = 'type';

    const TYPE_DYNAMIC = 0;
    const TYPE_STRING = 1;

    /**
     * The parsed filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Contains the details of the current filter name.
     *
     * @var string
     */
    protected $filterName = '';

    /**
     * Retains all characters for the current parsing segment.
     *
     * @var string
     */
    protected $currentSegment = '';

    /**
     * Indicates the analyzed type for the current filter input value.
     *
     * @var int
     */
    protected $currentType = self::TYPE_DYNAMIC;

    /**
     * A list of all parsed filter expression inputs.
     *
     * @var array
     */
    protected $filterInputs = [];

    /**
     * Indicates if the parser is parsing input values.
     *
     * @var bool
     */
    protected $isParsingInput = false;

    /**
     * Indicates if the parser is parsing a string input value.
     *
     * @var bool
     */
    protected $isParsingString = false;

    protected $isParsingGroupName = false;

    /**
     * A collection of all individual characters from the input string.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * The total number of individual characters from the input string.
     *
     * @var int
     */
    protected $inputLength = 0;

    /**
     * A list of all valid string escape sequences.
     *
     * @var string[]
     */
    protected $validEscapeCharacters = ['\'', '\\'];

    /**
     * The index within $tokens that the current string started.
     *
     * @var int
     */
    protected $stringStartPosition = -1;

    /**
     * The index within $tokens that the current input list started.
     *
     * @var int
     */
    protected $inputListStartPosition = -1;

    /**
     * A list of all filter group aliases.
     *
     * @var array
     */
    protected $filterGroups = [];

    /**
     * Converts an array of parsed filters back into a valid expression string.
     *
     * @param array $filters An array of parsed filters.
     * @return string
     */
    public static function convertToString($filters)
    {
        return implode(self::TOKEN_FILTER_DELIMITER, self::convertToFilterStrings($filters));
    }

    /**
     * Converts the parsed filters into an array of strings representing each filter value.
     *
     * @param array $filters The parsed filters.
     * @return string[]
     */
    public static function convertToFilterStrings($filters)
    {
        $filterStrings = [];

        foreach ($filters as $filter) {
            $filterStrings[] = self::convertFilterToString($filter);
        }

        return $filterStrings;
    }

    /**
     * Returns a string filter expression representation of a previously parsed filter.
     *
     * @param array $singleFilter The parsed filter.
     * @return string
     */
    public static function convertFilterToString($singleFilter)
    {
        return self::convertFilterToStringWithName($singleFilter)['expression'];
    }

    /**
     * Converts a parsed filter into an array containing the filter's string representation and name.
     *
     * @param array $singleFilter The parsed filter.
     * @return array
     */
    public static function convertFilterToStringWithName($singleFilter)
    {
        $filterName = $singleFilter['name'];
        $filterInput = $singleFilter['input'];
        $values = [];

        foreach ($filterInput as $input) {
            if ($input[self::KEY_TYPE] === self::TYPE_DYNAMIC) {
                $values[] = $input[self::KEY_VALUE];
            } else if ($input[self::KEY_TYPE] === self::TYPE_STRING) {
                $values[] = '\'' . self::escapeFilterString($input[self::KEY_VALUE]) . '\'';
            }
        }

        return [
            'name' => $filterName,
            'expression' => $filterName . self::TOKEN_INPUT_START . implode(self::TOKEN_INPUT_DELIMITER, $values) . self::TOKEN_INPUT_END
        ];
    }

    /**
     * Escapes strings and backslash characters for filter expressions.
     *
     * @param string $string The input string.
     * @return string
     */
    public static function escapeFilterString($string)
    {
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace('\'', '\\\'', $string);

        return $string;
    }

    /**
     * Maps the input parameters to their corresponding required parameters.
     *
     * @param array $requiredParameters The filter's required parameters.
     * @param array $inputParameters The input parameters.
     * @return array
     * @throws FilterException
     */
    public static function mapParameters($requiredParameters, $inputParameters)
    {
        if (count($requiredParameters) === 0 && count($inputParameters) === 0) {
            return [];
        }

        if (count($requiredParameters) > count($inputParameters)) {
            throw new FilterException('Unmatched parameter count.');
        }

        $reqParamLen = count($requiredParameters);
        $lastParamIndex = $reqParamLen - 1;

        $mappedParameters = [];

        for ($i = 0; $i < $reqParamLen; $i++) {
            $paramName = trim($requiredParameters[$i]);
            $paramValue = null;

            // We will expect this to be an array with 'value' and 'type'.
            $parserValue = array_shift($inputParameters);

            $paramValue = $parserValue[ExpressionParser::KEY_VALUE];

            if ($i === $lastParamIndex) {
                // Do we have any remaining input parameters to include?
                // If so, convert this parameter value to an array and
                // include the previously discovered parameter value.
                if (count($inputParameters) > 0) {
                    $thisValues = [$paramValue];

                    foreach ($inputParameters as $parameterValue) {
                        $thisValues[] = $parameterValue[self::KEY_VALUE];
                    }

                    $paramValue = $thisValues;
                }
            }

            $mappedParameters[$paramName] = $paramValue;
        }

        return $mappedParameters;
    }

    /**
     * Converts the details of a single filter into a valid filter expression string.
     *
     * @param string $filterName The filter name.
     * @param array $inputs The input values.
     * @return string
     */
    public static function build($filterName, $inputs)
    {
        $processedInputs = [];

        foreach ($inputs as $input) {
            if (Str::contains($input, ['\\', '\''])) {
                $processedInputs[] = self::TOKEN_STR_DELIMITER . self::escapeFilterString($input) . self::TOKEN_STR_DELIMITER;
            } else {
                $processedInputs[] = $input;
            }
        }

        return $filterName . self::TOKEN_INPUT_START . implode(self::TOKEN_INPUT_DELIMITER, $processedInputs) . self::TOKEN_INPUT_END;
    }

    /**
     * Builds an array that matches parser return values from the filter details.
     *
     * @param string $filterName The filter name.
     * @param array $inputs The input values.
     * @return array
     */
    public static function buildFilterArray($filterName, $inputs)
    {
        $processedInputs = [];

        foreach ($inputs as $input) {
            if (Str::contains($input, ['\\', '\''])) {
                $processedInputs[] = [
                    self::KEY_VALUE => self::TOKEN_STR_DELIMITER . self::escapeFilterString($input) . self::TOKEN_STR_DELIMITER,
                    self::KEY_TYPE => self::TYPE_STRING
                ];
            } else {
                $processedInputs[] = [
                    self::KEY_VALUE => $input,
                    self::KEY_TYPE => self::TYPE_DYNAMIC
                ];
            }
        }

        return [
            self::KEY_NAME => $filterName,
            self::KEY_INPUT => $processedInputs
        ];
    }

    /**
     * Determines if the parsed filters contains a filter with the provided name.
     *
     * @param string $filterName The filter name.
     * @param array $filters The parsed filters.
     * @return bool
     */
    public static function hasFilter($filterName, $filters)
    {
        foreach ($filters as $filter) {
            if (array_key_exists(self::KEY_NAME, $filter)) {
                if ($filter[self::KEY_NAME] === $filterName) {
                    return true;
                }
            }
        }

        return false;
    }

    public function setFilterGroups($filterGroups)
    {
        $this->filterGroups = $filterGroups;
    }

    /**
     * Parses the filter expression and returns an array of query filters.
     *
     * @param string $inputString The filter expression to parse.
     * @return array
     * @throws FilterParserException
     */
    public function parse($inputString)
    {
        // TODO: Do a pre-processing step to replace the @gorups with their actual values...
        $this->reset();

        $this->tokens = mb_str_split($inputString);
        $this->inputLength = count($this->tokens);

        for ($i = 0; $i < $this->inputLength; $i++) {
            $current = $this->tokens[$i];
            $next = null;
            $previous = null;

            if ($i > 0) {
                $previous = $this->tokens[$i - 1];
            }

            if (($i + 1) < $this->inputLength) {
                $next = $this->tokens[$i + 1];
            }

            if ($current === self::TOKEN_FILTER_DELIMITER) {
                if ($previous === null) {
                    throw new FilterParserException('Cannot start expression with TOKEN_FILTER_DELIMITER');
                } else if ($previous !== self::TOKEN_INPUT_END && !$this->isParsingString) {
                    if ($this->isParsingGroupName) {
                        if (array_key_exists($this->currentSegment, $this->filterGroups)) {
                            $referenceInput = $inputString;
                            $currentEnd = $i;
                            $refStart = $currentEnd - mb_strlen($this->currentSegment);
                            $newStart = mb_substr($referenceInput, 0, $refStart);
                            $newEnd = mb_substr($referenceInput, $currentEnd, $this->inputLength);

                            $newInput = $newStart . $this->filterGroups[$this->currentSegment] . $newEnd;

                            $this->reset();
                            return $this->parse($newInput);
                        } else {
                            throw new FilterParserException('Invalid filter group "' . $this->currentSegment . '" near position: ' . ($i + 1));
                        }
                    } else {
                        throw new FilterParserException('Unexpected TOKEN_FILTER_DELIMITER at character: ' . ($i + 1));
                    }
                }
            } else if ($current === self::TOKEN_INPUT_END && $previous === null) {
                throw new FilterParserException('Cannot start expression with TOKEN_INPUT_END');
            } else if ($current === self::TOKEN_INPUT_START && $previous === null) {
                throw new FilterParserException('Cannot start expression with TOKEN_INPUT_START');
            } else if ($current === self::TOKEN_STR_DELIMITER && $previous === null) {
                throw new FilterParserException('Cannot start expression with TOKEN_STR_DELIMITER');
            } else if ($current === self::TOKEN_INPUT_DELIMITER && $previous === null) {
                throw new FilterParserException('Cannot start expression with TOKEN_INPUT_DELIMITER');
            }

            if ($current === self::TOKEN_INPUT_START && !$this->isParsingString && $this->isParsingInput) {
                throw new FilterParserException('Unexpected TOKEN_INPUT_START at character: ' . ($i + 1));
            } else if ($current === self::TOKEN_INPUT_END && !$this->isParsingInput) {
                throw new FilterParserException('Unexpected TOKEN_INPUT_END at character: ' . ($i + 1));
            }

            if ($next === null) {
                if ($current !== self::TOKEN_INPUT_END && $previous !== self::TOKEN_INPUT_END) {
                    if ($this->isParsingGroupName === false) {
                        throw new FilterParserException('Reached end of input string without input list.');
                    }
                }

                if ($this->isParsingInput && $current !== self::TOKEN_INPUT_END) {
                    throw new FilterParserException('Unexpected end of input while parsing input list. ' .
                        'Input list started at character: ' . $this->inputListStartPosition);
                } else if ($this->isParsingString) {
                    throw new FilterParserException('Unexpected end of input while parsing string. ' .
                        'String started at character: ' . $this->stringStartPosition);
                }
            }

            if ($current === self::TOKEN_GROUP_START) {
                if (!$this->isParsingInput) {
                    if ($this->isParsingGroupName) {
                        throw new FilterParserException('Unexpected @ while parsing group name at position: ' . ($i + 1));
                    } else {
                        $this->isParsingGroupName = true;
                        $this->currentSegment .= $current;
                    }
                } else {
                    $this->currentSegment .= $current;
                }

                continue;
            } else if ($current === self::TOKEN_STR_ESCAPE) {
                if ($this->isParsingString && $next !== null) {
                    if (in_array($next, $this->validEscapeCharacters)) {
                        // Handles case of: where(property, =, '\'
                        if (($i + 2) >= $this->inputLength) {
                            throw new FilterParserException('Unexpected end of input while parsing string. ' .
                                'String started at character: ' . $this->stringStartPosition);
                        }

                        $this->currentSegment .= $next;
                        $i++;
                        continue;
                    } else {
                        throw new FilterParserException('Invalid string escape sequence at '
                            . ($i + 1) . ' ("\\' . $next . '").');
                    }
                }
            } else if ($current === self::TOKEN_STR_DELIMITER) {
                if ($this->isParsingString) {
                    $this->isParsingString = false;
                    $this->stringStartPosition = -1;

                    continue;
                }

                $this->isParsingString = true;
                $this->currentType = self::TYPE_STRING;
                $this->stringStartPosition = $i + 1;
                $this->currentSegment = '';

                continue;
            } else if ($current === self::TOKEN_FILTER_DELIMITER) {
                if ($this->isParsingString) {
                    $this->currentSegment .= $current;
                    continue;
                }

                if ($next === null) {
                    throw new FilterParserException('Unexpected end of input. Expecting new filter.');
                }

                $this->currentSegment = '';
                $this->filterName = '';
                $this->filterInputs = [];
                $this->currentType = self::TYPE_DYNAMIC;

                continue;
            } else if ($current === self::TOKEN_INPUT_END) {
                if ($this->isParsingString) {
                    $this->currentSegment .= $current;
                    continue;
                }

                $this->filterInputs[] = [
                    self::KEY_VALUE => $this->currentSegment,
                    self::KEY_TYPE => $this->currentType
                ];

                $this->currentSegment = '';
                $this->isParsingInput = false;
                $this->inputListStartPosition = -1;
                $this->currentType = self::TYPE_DYNAMIC;

                $trimmedInputs = [];

                foreach ($this->filterInputs as $input) {
                    $trimmedInputs[] = [
                        self::KEY_VALUE => trim($input[self::KEY_VALUE]),
                        self::KEY_TYPE => $input[self::KEY_TYPE]
                    ];
                }

                $this->filters[] = [
                    self::KEY_NAME => $this->filterName,
                    self::KEY_INPUT => $trimmedInputs
                ];

                $this->filterName = '';
                $this->filterInputs = [];

                continue;
            } else if ($current === self::TOKEN_INPUT_DELIMITER) {

                if ($this->isParsingString) {
                    $this->currentSegment .= $current;
                    continue;
                }

                if ($this->isParsingInput === false) {
                    throw new FilterParserException('Unexpected TOKEN_INPUT_DELIMITER ' .
                        'outside of input list at character: ' . ($i + 1));
                }

                $this->filterInputs[] = [
                    self::KEY_VALUE => $this->currentSegment,
                    self::KEY_TYPE => $this->currentType
                ];

                $this->currentSegment = '';
                $this->currentType = self::TYPE_DYNAMIC;

                continue;
            } else if ($current === self::TOKEN_INPUT_START) {
                if ($this->isParsingString) {
                    $this->currentSegment .= $current;
                    continue;
                }

                $this->filterName = $this->currentSegment;
                $this->currentSegment = '';
                $this->isParsingInput = true;
                $this->inputListStartPosition = $i + 1;
                $this->currentType = self::TYPE_DYNAMIC;

                continue;
            }

            $this->currentSegment .= $current;

            if ($next === null) {
                if ($this->isParsingGroupName) {
                    if (array_key_exists($this->currentSegment, $this->filterGroups)) {
                        $referenceInput = $inputString;
                        $currentEnd = $i + 1;
                        $refStart = $currentEnd - mb_strlen($this->currentSegment);
                        $newStart = mb_substr($referenceInput, 0, $refStart);
                        $newEnd = mb_substr($referenceInput, $currentEnd, $this->inputLength);

                        $newInput = $newStart . $this->filterGroups[$this->currentSegment] . $newEnd;

                        $this->reset();
                        return $this->parse($newInput);
                    } else {
                        throw new FilterParserException('Invalid filter group "' . $this->currentSegment . '" at end of input.');
                    }
                }
            }
        }

        return $this->filters;
    }

    /**
     * Resets the internal parser state.
     */
    private function reset()
    {
        $this->filters = [];
        $this->filterName = '';
        $this->currentSegment = '';
        $this->currentType = self::TYPE_DYNAMIC;
        $this->filterInputs = [];

        $this->isParsingInput = false;
        $this->isParsingString = false;
        $this->isParsingGroupName = false;

        $this->tokens = [];
        $this->inputLength = 0;
        $this->stringStartPosition = -1;
        $this->inputListStartPosition = -1;
    }

}
