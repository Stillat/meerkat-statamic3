<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * Interface MarkdownParserContract
 *
 * Provides a consistent API for managing Markdown-formatted documents.
 *
 * @since 2.0.0
 */
interface MarkdownParserContract
{
    /**
     * Parses the provided string document and returns string value.
     *
     * @param  string  $content
     * @return string
     */
    public function parseDocument($content);

    /**
     * Removes problematic HTML elements from the provided document.
     *
     * @param  string  $content
     * @return string
     */
    public function cleanDocument($content);

    /**
     * Parses and cleans the provided content and returns the result.
     *
     * @param  string  $content
     * @return string
     */
    public function parse($content);

    /**
     * Parses the provided string content and merges the results into the provided data container array.
     *
     * @param  string  $stringContent The content to parse.
     * @return void
     */
    public function parseStringAndMerge($stringContent, array &$dataContainer);
}
