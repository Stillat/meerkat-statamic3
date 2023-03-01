<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;

/**
 * Trait UsesMarkdownParser
 *
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
     * The Markdown parser implementation instance.
     *
     * @var MarkdownParserContract|null
     */
    protected $markdownParser = null;

    /**
     * Returns access to an MarkdownParserContract instance.
     *
     * @return MarkdownParserContract
     *
     * @throws InconsistentCompositionException
     */
    protected function getMarkdownParser()
    {
        if ($this->markdownParser !== null) {
            return $this->markdownParser;
        }

        throw InconsistentCompositionException::make('markdownParser', __CLASS__);
    }

    /**
     * Sets the Markdown parser implementation instance.
     *
     * @param  MarkdownParserContract  $markdownParser The parser instance.
     */
    public function setMarkdownParser($markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }
}
