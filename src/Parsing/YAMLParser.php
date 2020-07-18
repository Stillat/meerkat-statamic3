<?php

namespace Stillat\Meerkat\Parsing;

use Statamic\Yaml\Yaml;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Parsing\AbstractYAMLParser;

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

    public function toYaml($content)
    {
        return $this->statamicParser->dump($content);
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

        return $this->statamicParser->parse($content);
    }

}
