<?php

namespace Stillat\Meerkat\Parsing;

use Statamic\Markdown\Parser;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Parsing\AbstractMarkdownParser;

/**
 * Class MarkdownParser
 *
 * Handles interactions between Statamic's Markdown parser and Meerkat Core.
 *
 * @package Stillat\Meerkat\Parsing
 * @since 2.0.0
 */
class MarkdownParser extends AbstractMarkdownParser implements MarkdownParserContract
{

    /**
     * The Statamic Markdown parser instance.
     *
     * @var Parser
     */
    private $statamicParser = null;

    public function __construct(Configuration $config, Parser $markdownParser)
    {
        parent::__construct($config);

        $this->statamicParser = $markdownParser;
    }

    /**
     * Parses the provided string document and returns string value.
     *
     * @param string $content
     *
     * @return string
     */
    public function parseDocument($content)
    {
        return $this->statamicParser->parse($content);
    }

}
