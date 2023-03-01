<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface DateParserContract
 *
 * Provides Meerkat Core with the ability to parse dates from arbitrary text input.
 *
 * @since 2.0.4
 */
interface DateParserContract
{
    /**
     * Converts the input text into a UNIX timestamp.
     *
     * @param  string  $dateInput The date input.
     * @return int
     */
    public function getTimestamp($dateInput);
}
