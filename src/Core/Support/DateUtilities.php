<?php

namespace Stillat\Meerkat\Core\Support;

/**
 * Class DateUtilities
 *
 * Provides utilities for working with timestamps.
 *
 * @since 2.0.0
 */
class DateUtilities
{
    /**
     * Gets the number of days between two timestamps.
     *
     * @param  int  $recentTimestamp The newer timestamp.
     * @param  int  $olderTimestamp The older timestamp.
     * @return false|float
     */
    public static function daysBetween($recentTimestamp, $olderTimestamp)
    {
        $diff = $recentTimestamp - $olderTimestamp;

        return round($diff / (60 * 60 * 24));
    }
}
