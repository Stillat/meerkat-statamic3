<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface SanitationManagerContract
 *
 * Provides a consistent API for interacting with multiple input sanitizers.
 *
 * @package Stillat\Meerkat\Core\Contracts\Parsing
 * @since 2.0.0
 */
interface SanitationManagerContract
{

    /**
     * Registers a new input sanitizer.
     *
     * @param OutputSanitizerContract $sanitizer The sanitizer instance.
     * @return void
     */
    public function registerSanitizer(OutputSanitizerContract $sanitizer);

    /**
     * Sanitizes the input value using all registered sanitizers.
     *
     * @param string $input The input value to sanitize.
     * @return string
     */
    public function sanitize($input);

    /**
     * Sanitizes the provided array's values using all reigstered sanitizers.
     *
     * @param array $array The array of values to sanitize.
     * @return array
     */
    public function sanitizeArrayValues($array);

}
