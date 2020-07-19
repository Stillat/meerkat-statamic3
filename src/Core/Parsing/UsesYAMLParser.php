<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\InconsistentCompositionException;

/**
 * Trait UsesYAMLParser
 *
 * Provides access to an AbstractYAMLParser instance
 *
 * Presence of this trait indicates that the eventual
 * implementation should provide access to a parser.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
trait UsesYAMLParser
{

    /**
     * The YAMLParserContract implementation instance.
     *
     * @var YAMLParserContract|null
     */
    protected $yamlParser = null;

    /**
     * Returns access to an AbstractYAMLParser instance.
     *
     * @return YAMLParserContract
     * @throws InconsistentCompositionException
     */
    protected function getYamlParser()
    {
        if ($this->yamlParser !== null) {
            return $this->yamlParser;
        }

        throw InconsistentCompositionException::make('yamlParser', __CLASS__);
    }

    /**
     * Sets the YAMLParserContract implementation instance.
     *
     * @param YAMLParserContract $yamlParser The parser instance.
     */
    public function setYamlParser($yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

}
