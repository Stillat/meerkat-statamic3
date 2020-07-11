<?php

namespace Stillat\Meerkat\Core\Parsing;

/**
 * Provides access to an AbstractYAMLParser instance
 *
 * Presence of this trait indicates that the eventual
 * implementation should provide access to a parser.
 *
 * @since 3.0..0
 */
trait UsesYAMLParser
{

    /**
     * Returns access to an AbstractYAMLParser instance.
     *
     * @return \Stillat\Meerkat\Core\Parsing\AbstractYAMLParser
     */
    protected function getYamlParser()
    {
        return $this->yamlParser;
    }
}
