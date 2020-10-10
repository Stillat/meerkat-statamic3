<?php

namespace Stillat\Meerkat\Parsing;

use Carbon\Carbon;
use Stillat\Meerkat\Core\Contracts\Parsing\DateParserContract;

/**
 * Class CarbonDateParser
 *
 * Utilizes Carbon to retrieve UNIX timestamps from input strings.
 *
 * @package Stillat\Meerkat\Parsing
 * @since 2.0.4
 */
class CarbonDateParser implements DateParserContract
{

    /**
     * Converts the input text into a UNIX timestamp.
     *
     * @param string $dateInput The date input.
     * @return int
     */
    public function getTimestamp($dateInput)
    {
        return Carbon::parse($dateInput)->getTimestamp();
    }

}
