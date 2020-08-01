<?php

namespace Stillat\Meerkat\Parsing;

use Statamic\Yaml\ParseException;
use Statamic\Yaml\Yaml;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Parsing\AbstractYAMLParser;

/**
 * Class YAMLParser
 *
 * Provides utilities for parsing and encoding YAML documents.
 *
 * @package Stillat\Meerkat\Parsing
 * @since 2.0.0
 */
class YAMLParser extends AbstractYAMLParser implements YAMLParserContract
{

    /**
     * The Statamic Yaml instance.
     *
     * @var Yaml|null
     */
    protected $statamicParser = null;

    public function __construct(Yaml $yaml)
    {
        $this->statamicParser = $yaml;
    }

    /**
     * Converts the provided meta-data and content to YAML.
     *
     * @param array $data The content meta-data.
     * @param string $content The content to save.
     * @return string
     */
    public function toYaml($data, $content)
    {
        return $this->statamicParser->dump($data, $content);
    }

    /**
     * Parses the provided string document and returns a value array.
     *
     * @param string $content The content to parse.
     * @return array
     * @throws \Statamic\Yaml\ParseException
     */
    public function parseDocument($content)
    {
        if ($content === null || mb_strlen(trim($content)) === 0) {
            return [];
        }

        // If the site is running in debug mode, we will
        // allow the exception to be thrown. Otherwise
        // we log a warning and continue on running.
        $isDebug = false;// config('app.debug');

        if ($isDebug === true) {
            return $this->statamicParser->parse($content);
        } else {
            try {
                return $this->statamicParser->parse($content);
            } catch (ParseException $parseException) {
                // TODO: Emit warning.
                return [];
            }
        }
    }

}
