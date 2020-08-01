<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface OutputSanitizer
 *
 * Provides a consistent API for data sanitization.
 *
 * @package Stillat\Meerkat\Core\Contracts\Parsing
 * @since 2.0.0
 */
interface OutputSanitizerContract
{

    /**
     * Sanitizes the input value.
     *
     * @param string $value The value to sanitize.
     * @return string
     */
    public function sanitize($value);

}
