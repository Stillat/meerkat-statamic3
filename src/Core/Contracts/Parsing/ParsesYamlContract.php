<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

interface ParsesYamlContract
{

    /**
     * Sets the YAMLParserContract implementation instance.
     *
     * @param YAMLParserContract $yamlParser The parser instance.
     */
    public function setYamlParser($yamlParser);

}