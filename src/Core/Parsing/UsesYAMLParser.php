<?php

namespace Stillat\Meerkat\Core\Parsing;

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
     * Returns access to an AbstractYAMLParser instance.
     *
     * @return AbstractYAMLParser
     * @throws InconsistentCompositionException
     */
    protected function getYamlParser()
    {
        if (property_exists($this, 'yamlParser')) {
            return $this->yamlParser;
        }

        throw InconsistentCompositionException::make('yamlParser', __CLASS__);
    }

}
