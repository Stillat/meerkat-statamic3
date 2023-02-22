<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\OutputSanitizerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;

/**
 * Class SanitationManager
 *
 * Provides utilities to sanitize user-provided input values.
 *
 * @since 2.0.0
 */
class SanitationManager implements SanitationManagerContract
{
    /**
     * A collection of sanitizer instances.
     *
     * @var OutputSanitizerContract[]
     */
    protected $sanitizers = [];

    /**
     * The array keys to sanitize.
     *
     * @var array
     */
    protected $valuesToSanitize = [];

    public function __construct()
    {
        $this->valuesToSanitize[] = AuthorContract::KEY_NAME;
        $this->valuesToSanitize[] = AuthorContract::KEY_EMAIL_ADDRESS;
        $this->valuesToSanitize[] = CommentContract::KEY_CONTENT;
    }

    /**
     * Registers a new input sanitizer.
     *
     * @param  OutputSanitizerContract  $sanitizer The sanitizer instance.
     * @return void
     */
    public function registerSanitizer(OutputSanitizerContract $sanitizer)
    {
        $this->sanitizers[] = $sanitizer;
    }

    /**
     * Sanitizes the provided array's values using all reigstered sanitizers.
     *
     * @param  array  $array The array of values to sanitize.
     * @return array
     */
    public function sanitizeArrayValues($array)
    {
        foreach ($array as $key => &$value) {
            if (in_array($key, $this->valuesToSanitize)) {
                $array[$key] = $this->sanitize($value);
            }
        }

        return $array;
    }

    /**
     * Sanitizes the input value using all registered sanitizers.
     *
     * @param  string  $input The input value to sanitize.
     * @return string
     */
    public function sanitize($input)
    {
        $sanitizedValue = $input;

        foreach ($this->sanitizers as $sanitizer) {
            $sanitizedValue = $sanitizer->sanitize($sanitizedValue);
        }

        return $sanitizedValue;
    }
}
