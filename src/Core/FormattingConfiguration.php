<?php

namespace Stillat\Meerkat\Core;

/**
 * Class FormattingConfiguration
 *
 * Contains comment-related formatting configuration items.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
class FormattingConfiguration extends ConfigurationContainer
{

    /**
     * Specifies the format string that will be used when formatting comment's date properties.
     *
     * Used by:
     *    - Stillat\Meerkat\Core\Storage\Data\ThreadCommentRetriever::getComments()
     *
     * @var string
     */
    public $commentDateFormat = 'Y-m-d h:m:s A';

    /**
     * A list of HTML tags to keep from parsed Markdown documents
     *
     * Used by:
     *    - Stillat\Meerkat\Core\Parsing\AbstractMarkdownParser::cleanDocument($content)
     *
     * @var string
     */
    public $tagsToKeep = ['strong', 'em'];

}
