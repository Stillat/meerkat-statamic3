<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;

/**
 * Class AbstractYAMLParser
 *
 * Provides a base abstract implementation of YAMLParserContract
 *
 * This abstract implementation handles the parseAndMerge responsibility
 * of the YAMLParserContract. Use this to kick-start custom parsers.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.0
 */
abstract class AbstractYAMLParser implements YAMLParserContract
{

    /**
     * Parses the provided string document and merges the results into the provided data container array.
     *
     * @param string $content
     * @param array  $dataContainer
     * @return void
     */
    public function parseAndMerge($content, array &$dataContainer)
    {
        $parsedDocument = $this->parseDocument($content);
        $dataContainer = array_merge($parsedDocument, $dataContainer);

        if (array_key_exists(CommentContract::KEY_COMMENT, $dataContainer) && !array_key_exists(CommentContract::KEY_CONTENT, $dataContainer)) {
            $dataContainer[CommentContract::KEY_CONTENT] = $dataContainer[CommentContract::KEY_COMMENT];
            $dataContainer[CommentContract::KEY_RAW_CONTENT] = $dataContainer[CommentContract::KEY_CONTENT];
        }

        if (array_key_exists(CommentContract::KEY_COMMENT, $parsedDocument)) {
            $dataContainer[CommentContract::KEY_COMMENT_MARKDOWN] = $parsedDocument[CommentContract::KEY_COMMENT];
        }

        if (array_key_exists(CommentContract::KEY_CONTENT, $parsedDocument)) {
            $dataContainer[CommentContract::KEY_COMMENT_MARKDOWN] = $parsedDocument[CommentContract::KEY_CONTENT];
        }
    }
}
