<?php

namespace Stillat\Meerkat\Core\Parsing;

/**
 * Provides access to an AbstractMarkdownParser instance
 *
 * Presence of this trait indicates that the eventual
 * implementation should provide access to a parser.
 *
 * @since 2.0.0
 */
trait UsesMarkdownParser
{

    /**
     * Returns access to an AbstractMarkdownParser instance.
     *
     * @return \Stillat\Meerkat\Core\Parsing\AbstractMarkdownParser
     */
    protected function getMarkdownParser()
    {
        return $this->markdownParser;
    }
}
