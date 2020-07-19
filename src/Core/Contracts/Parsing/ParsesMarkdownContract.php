<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;

/**
 * Interface ParsesMarkdownContract
 *
 * Provides a consistent API for managing Markdown parser instances.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
interface ParsesMarkdownContract
{
    /**
     * Sets the Markdown parser implementation instance.
     *
     * @param MarkdownParserContract $markdownParser The parser instance.
     */
    public function setMarkdownParser($markdownParser);

}