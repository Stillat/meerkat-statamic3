<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\PrototypeParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;

/**
 * Class FullCommentPrototypeParser
 *
 * Utilizes a full YAML-spec parser to produce a comment prototype.
 *
 * This prototype parser produces the most consistent results across a wide
 * range of Comment data structures, but with a higher performance cost.
 *
 * @since 2.1.6
 */
class FullCommentPrototypeParser implements PrototypeParserContract
{
    /**
     * The prototype comment elements to parse.
     *
     * @var array
     */
    protected $prototypeElements = [];

    /**
     * The Meerkat Core configuration.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The prototype elements that should be converted to boolean values.
     *
     * @var array
     */
    protected $truthyPrototypeElements = [];

    /**
     * The YAMLParserContract implementation instance.
     *
     * @var YAMLParserContract
     */
    protected $yamlParser = null;

    public function __construct(YAMLParserContract $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    /**
     * Sets the comment's truthy prototype elements.
     *
     * @param  array  $elements The truthy prototype elements.
     */
    public function setTruthyElements($elements)
    {
        $this->truthyPrototypeElements = $elements;
    }

    /**
     * Sets the Meerkat Core configuration instance.
     *
     * @param  Configuration  $configuration The configuration.
     */
    public function setConfig(Configuration $configuration)
    {
        $this->config = $configuration;
    }

    /**
     * Sets the prototype elements.
     *
     * @param  array  $elements The prototype elements.
     */
    public function setPrototypeElements($elements)
    {
        $this->prototypeElements = $elements;
    }

    /**
     * Retrieves only the core meta-data for the comment.
     *
     * Supplemental data and content are ignored during this phase.
     *
     * @param  string  $path The full path to the comment data.
     * @return array
     */
    public function getCommentPrototype($path)
    {
        $contents = file_get_contents($path);
        $parsedComment = $this->yamlParser->parseDocument($contents);
        $commentContent = '';
        $alreadyFoundContent = false;

        if (array_key_exists(CommentContract::KEY_LEGACY_COMMENT, $parsedComment)) {
            $commentContent = $parsedComment[CommentContract::KEY_LEGACY_COMMENT];
            $alreadyFoundContent = true;

            unset($parsedComment[CommentContract::KEY_LEGACY_COMMENT]);
        } elseif (array_key_exists(CommentContract::KEY_CONTENT, $parsedComment)) {
            $commentContent = $parsedComment[CommentContract::KEY_CONTENT];

            unset($parsedComment[CommentContract::KEY_CONTENT]);
        }

        // Reset some types.
        if (array_key_exists(CommentContract::KEY_ID, $parsedComment)) {
            if (is_int($parsedComment[CommentContract::KEY_ID])) {
                $parsedComment[CommentContract::KEY_ID] = strval($parsedComment[CommentContract::KEY_ID]);
            }
        }

        return [
            LocalCommentStorageManager::KEY_HEADERS => $parsedComment,
            LocalCommentStorageManager::KEY_RAW_HEADERS => $parsedComment,
            LocalCommentStorageManager::KEY_CONTENT => $commentContent,
            LocalCommentStorageManager::KEY_NEEDS_MIGRATION => $alreadyFoundContent,
        ];
    }
}
