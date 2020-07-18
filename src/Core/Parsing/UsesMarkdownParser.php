<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\InconsistentCompositionException;

/**
 * Trait UsesMarkdownParser
 *
 * Provides access to an AbstractMarkdownParser instance
 *
 * Presence of this trait indicates that the eventual
 * implementation should provide access to a parser.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
trait UsesMarkdownParser
{

    /**
     * Returns access to an AbstractMarkdownParser instance.
     *
     * @return AbstractMarkdownParser
     *
     * @throws InconsistentCompositionException
     */
    protected function getMarkdownParser()
    {
        if (property_exists($this, 'markdownParser')) {
            return $this->markdownParser;
        }

        throw InconsistentCompositionException::make('markdownParser', __CLASS__);
    }

}
