<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;

/**
 * Class MarkdownParserFactory
 *
 * Allows Meerkat Core internals to access a shared markdown parser instance.
 *
 * @since 2.0.0
 */
class MarkdownParserFactory
{
    /**
     * A shared MarkdownParserContract implementation instance.
     *
     * @var null|MarkdownParserContract
     */
    public static $instance = null;

    /**
     * Indicates if a shared SanitationManagerContract implementation exists.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (MarkdownParserFactory::$instance != null) {
            return true;
        }

        return false;
    }
}
