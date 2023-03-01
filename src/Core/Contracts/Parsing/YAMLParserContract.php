<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface YAMLParserContract
 *
 * Provides a consistent API for managing YAML-formatted documents.
 *
 * @since 2.0.0
 */
interface YAMLParserContract
{
    /**
     * Parses the provided string document and returns a value array.
     *
     * @param  string  $content
     * @return array
     */
    public function parseDocument($content);

    /**
     * Converts the provided data into its YAML representation.
     *
     * @param  mixed  $attributes The data to convert.
     * @param  string  $content The content to convert.
     * @return string
     */
    public function toYaml($attributes, $content);

    /**
     * Parses the provided string document and merges the results into the provided data container array.
     *
     * @param  string  $content
     * @return void
     */
    public function parseAndMerge($content, array &$dataContainer);
}
