<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

use Stillat\Meerkat\Core\Configuration;

/**
 * Interface PrototypeParserContract
 *
 * Represents a parser capable of converting a YAML
 * text file into a Meerkat Comment prototype.
 *
 * @since 2.1.6
 */
interface PrototypeParserContract
{
    /**
     * Sets the comment's truthy prototype elements.
     *
     * @param  array  $elements The truthy prototype elements.
     */
    public function setTruthyElements($elements);

    /**
     * Sets the Meerkat Core configuration instance.
     *
     * @param  Configuration  $configuration The configuration.
     */
    public function setConfig(Configuration $configuration);

    /**
     * Sets the prototype elements.
     *
     * @param  array  $elements The prototype elements.
     */
    public function setPrototypeElements($elements);

    /**
     * Retrieves only the core meta-data for the comment.
     *
     * Supplemental data and content are ignored during this phase.
     *
     * @param  string  $path The full path to the comment data.
     * @return array
     */
    public function getCommentPrototype($path);
}
