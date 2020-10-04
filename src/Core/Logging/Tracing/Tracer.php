<?php

namespace Stillat\Meerkat\Core\Logging\Tracing;

/**
 * Class Tracer
 *
 * Provides simple wrappers and utilities around PHP back-traces.
 *
 * @package Stillat\Meerkat\Core\Logging\Tracing
 * @since 2.0.0
 */
class Tracer
{
    const KEY_CLASS = 'class';
    const KEY_FUNCTION = 'function';

    /**
     * Attempts to locate a relevant method call from a call stack.
     *
     * @return mixed|string|null
     */
    public static function getCallingMethod()
    {
        $method = null;

        $backTraceItems = debug_backtrace(false, 3);
        $lastItem = array_pop($backTraceItems);

        if ($lastItem !== null) {
            if (array_key_exists(self::KEY_CLASS, $lastItem)) {
                $method = $lastItem[self::KEY_CLASS];

                if (array_key_exists(self::KEY_FUNCTION, $lastItem)) {
                    $method .= '::' . $lastItem[self::KEY_FUNCTION];
                }
            }
        }

        return $method;
    }

}
