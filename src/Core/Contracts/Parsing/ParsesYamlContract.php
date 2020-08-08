<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface ParsesYamlContract
 *
 * Provides a consistent API for indicating an object requires a YAML parser.
 *
 * @package Stillat\Meerkat\Core\Contracts\Parsing
 * @since 2.0.0
 */
interface ParsesYamlContract
{

    /**
     * Sets the YAMLParserContract implementation instance.
     *
     * @param YAMLParserContract $yamlParser The parser instance.
     */
    public function setYamlParser($yamlParser);

}
