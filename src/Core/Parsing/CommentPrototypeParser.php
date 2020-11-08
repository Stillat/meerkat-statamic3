<?php

namespace Stillat\Meerkat\Core\Parsing;

use Exception;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\PrototypeParserContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class CommentPrototypeParser
 *
 * Parses comment files and returns an array containing the prototypical data to work with comment data.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.11
 */
class CommentPrototypeParser implements PrototypeParserContract
{
    const UNIX_EPOCH_ID = '0000000000';

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
     * The number of times the parser has seen the `---\n` sequence.
     *
     * @var int
     */
    private $headerDelimiterObserved = 0;

    /**
     * All of the prototype headers discovered during parsing.
     *
     * @var array
     */
    private $headers = [];

    /**
     * All of the headers discovered during parsing.
     *
     * @var array
     */
    private $rawHeaders = [];

    /**
     * Indicates if the parser should collect all headers, or just prototype headers.
     *
     * TRUE: Collects all headers.
     * FALSE: Collects only prototype headers.
     *
     * @var bool
     */
    private $collectHeaders = true;

    /**
     * The resolved comment content.
     *
     * @var string
     */
    private $content = '';

    /**
     * The current content line being processed.
     *
     * @var int
     */
    private $contentLine = -1;

    /**
     * Indicates if the parser already found a comment's content.
     *
     * This will be true if the `comment: ''` is present in the comment header.
     *
     * @var bool
     */
    private $alreadyFoundContent = false;

    /**
     * Indicates if the parser already found the file's encoding.
     *
     * @var bool
     */
    private $hasCheckedEncoding = false;

    /**
     * The storage file's detected encoding, if any.
     *
     * @var null|string
     */
    private $detectedEncoding = null;

    /**
     * Indicates if the full content parser should be used.
     *
     * Use of the full content parser is not ideal, since it does not
     * offer the large-file protections that the UTF8 and UTF8-BOM
     * parser offer. eg., the UTF8 parser will not load a 100mb
     * file into memory, and only grab just enough data to
     * continue with the comment gathering processes.
     *
     * @var bool
     */
    private $useFullContentParser = false;

    /**
     * Indicates if the parser should continue to look for comment headers.
     *
     * @var bool
     */
    private $doProcessHeaders = true;

    /**
     * A list of all the comment nodes that failed to parse.
     *
     * @var string[]
     */
    private $failedNodes = [];

    /**
     * Sets the comment's truthy prototype elements.
     *
     * @param array $elements The truthy prototype elements.
     */
    public function setTruthyElements($elements)
    {
        $this->truthyPrototypeElements = $elements;
    }

    /**
     * Indicates if any comment nodes failed to parse.
     *
     * @return bool
     */
    public function hasFailedNodes()
    {
        return count($this->failedNodes) > 0;
    }

    /**
     * Returns all of the failed nodes.
     *
     * @return string[]
     */
    public function getFailedNodes()
    {
        return $this->failedNodes;
    }

    /**
     * Sets the Meerkat Core configuration instance.
     *
     * @param Configuration $configuration The configuration.
     */
    public function setConfig(Configuration $configuration)
    {
        $this->config = $configuration;
    }

    /**
     * Sets the prototype elements.
     *
     * @param array $elements The prototype elements.
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
     * @param string $path The full path to the comment data.
     * @return array
     */
    public function getCommentPrototype($path)
    {
        $this->reset();
        $handle = fopen($path, 'r');

        $bom = pack("CCC", 0xef, 0xbb, 0xbf);

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if ($this->hasCheckedEncoding === false) {
                    if (0 === strncmp($line, $bom, 3)) {
                        $line = substr($line, 3);
                    }

                    if (trim($line) === '---') {
                        $this->useFullContentParser = false;
                        $this->hasCheckedEncoding = true;
                    } else {
                        $this->detectedEncoding = mb_detect_encoding($line);
                        $this->hasCheckedEncoding = true;

                        if ($this->detectedEncoding !== null) {
                            $this->useFullContentParser = true;
                            break;
                        }
                    }
                }

                if (0 === strncmp($line, $bom, 3)) {
                    $line = substr($line, 3);
                }

                $continue = $this->processLine($line);

                if ($continue === false) {
                    break;
                }
            }

            fclose($handle);
        }

        if ($this->useFullContentParser) {
            try {
                $this->convertWithEncoding($path, $this->detectedEncoding);
            } catch (Exception $e) {
                $this->failedNodes[] = $this->getIdFromPath($path);

                ExceptionLoggerFactory::log($e);
                LocalErrorCodeRepository::logCodeMessage(Errors::COMMENT_PARSER_NODE_FAILURE, $e->getMessage());
            }
        }

        $this->ensureIdIntegrity($path);

        $this->fillSupplementalDataIfRequired($path);

        $this->content = str_replace('\r\n', "\n", $this->content);

        return [
            LocalCommentStorageManager::KEY_HEADERS => $this->headers,
            LocalCommentStorageManager::KEY_RAW_HEADERS => $this->rawHeaders,
            LocalCommentStorageManager::KEY_CONTENT => $this->content,
            LocalCommentStorageManager::KEY_NEEDS_MIGRATION => $this->alreadyFoundContent
        ];
    }

    /**
     * Resets the internal parser state.
     *
     * Note: This method does not reset the failed nodes.
     */
    public function reset()
    {
        $this->doProcessHeaders = true;
        $this->headerDelimiterObserved = 0;
        $this->headers = [];
        $this->rawHeaders = [];
        $this->collectHeaders = true;
        $this->content = '';
        $this->contentLine = -1;

        $this->alreadyFoundContent = false;
        $this->hasCheckedEncoding = false;
        $this->detectedEncoding = null;
        $this->useFullContentParser = false;

        $this->headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = false;
    }

    /**
     * Attempts to process the provided content line.
     *
     * @param string $line The line to process.
     * @return bool
     */
    private function processLine($line)
    {
        $trimLine = trim($line);
        $this->doProcessHeaders = true;

        if ($trimLine === '---') {
            $this->headerDelimiterObserved += 1;

            if ($this->headerDelimiterObserved === 1) {
                $this->doProcessHeaders = false;
            } else {
                $this->doProcessHeaders = false;
                $this->alreadyFoundContent = false;
            }
        }

        if ($this->headerDelimiterObserved >= 2) {
            $this->collectHeaders = false;
            $this->doProcessHeaders = false;
        }

        if ($this->doProcessHeaders) {
            if ($this->collectHeaders) {
                $this->rawHeaders[] = $line;
            }

            $protoParts = explode(': ', $trimLine, 2);

            if (is_array($protoParts) == true && count($protoParts) == 2) {
                if ($protoParts[0] == 'comment') {
                    if (mb_strlen($protoParts[1]) > $this->config->hardCommentLengthCap) {
                        $this->content = mb_substr($protoParts[1], 0, $this->config->hardCommentLengthCap);
                        $this->headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                    } else {
                        $this->content = $protoParts[1];
                    }

                    $this->alreadyFoundContent = true;
                }

                if (in_array($protoParts[0], $this->prototypeElements)) {
                    $this->headers[$protoParts[0]] = $this->cleanAttributeValue($protoParts[1]);

                    if (in_array($protoParts[0], $this->truthyPrototypeElements)) {
                        $this->headers[$protoParts[0]] = TypeConversions::getBooleanValue($protoParts[1]);
                    }
                }
            }
        }

        if ($this->doProcessHeaders == false && $this->collectHeaders == false && $this->alreadyFoundContent == false) {
            $this->contentLine += 1;

            if ($this->contentLine > 0) {
                $this->content .= $line;

                if (mb_strlen($this->content) > $this->config->hardCommentLengthCap) {
                    $this->content = mb_substr($this->content, 0, $this->config->hardCommentLengthCap);
                    $this->headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Cleans an attribute value to make it consistent and usable.
     *
     * @param string $attributeValue The value to clean.
     * @return string
     */
    private function cleanAttributeValue($attributeValue)
    {
        $attributeValue = ltrim($attributeValue, '"\'');
        $attributeValue = rtrim($attributeValue, '"\'');

        return $attributeValue;
    }

    /**
     * Attempts to convert the comment's content with the provided encoding.
     *
     * @param string $path The file path.
     * @param string $encoding The content encoding.
     */
    private function convertWithEncoding($path, $encoding)
    {
        $contents = file_get_contents($path);

        if (Str::startsWith($contents, '---') === false) {
            $contents = "---\n".$contents;
        }
        if (Str::endsWith($contents, '---') === false) {
            $contents = $contents."\n---";
        }

        $encoding = $this->getFormatEncoding($contents, $encoding);

        if ($encoding === false) {
            unset($contents);
            return;
        }

        $lines = Str::toArray(trim(mb_convert_encoding($contents, 'UTF-8', $encoding)));

        // Rewrite the first line.
        $lines[0] = '---';

        foreach ($lines as $line) {
            $continue = $this->processLine($line);

            if ($continue === false) {
                break;
            }
        }

        unset($contents);
        unset($lines);
    }

    /**
     * Attempts to improve the encoding guess based on what the opening YAML header looks like.
     *
     * @param string $contents The content to analyze.
     * @param string $encoding The already guessed encoding.
     * @return string
     */
    private function getFormatEncoding($contents, $encoding)
    {
        $utf16leBom = chr(0xFF) . chr(0xFE);
        $utf16beBom = chr(0xFE) . chr(0xFF);
        $firstTwo = substr($contents, 0, 2);

        if ($firstTwo === $utf16beBom) {
            return 'UTF-16BE';
        } elseif ($firstTwo === $utf16leBom && $encoding === false) {
            $encoding = 'UTF-16LE';
        } elseif (Str::startsWith($contents, "-\x00-\x00-\x00") && $encoding === 'ASCII') {
            $encoding = 'UTF-16LE';
        } elseif (Str::startsWith($contents, "\x00-\x00-\x00-\x00") && $encoding === 'ASCII') {
            $encoding = 'UTF-16BE';
        }

        return $encoding;
    }

    /**
     * Attempts to resolve a comment's identifier based on its storage path.
     *
     * @param string $path The comment's storage path.
     * @return string|null
     */
    private function getIdFromPath($path)
    {
        $parts = explode('/', dirname($path));

        if (is_array($parts) && count($parts) > 0) {
            return $parts[count($parts) - 1];
        }

        return null;
    }

    /**
     * Protects against missing comment identifiers, or unexpected identifiers.
     *
     * @param string $path The comment's storage path.
     */
    private function ensureIdIntegrity($path)
    {
        $id = $this->getIdFromPath($path);

        if ($id === null) {
            $id = self::UNIX_EPOCH_ID;
            LocalErrorCodeRepository::logCodeMessage(Errors::COMMENT_PARSER_ID_INTEGRITY_LOST, $path);
        }

        if (array_key_exists(CommentContract::KEY_ID, $this->headers) === false) {
            $this->headers[CommentContract::KEY_ID] = $id;
            $this->rawHeaders[CommentContract::KEY_ID] = $id;
        } else {
            $headerId = $this->headers[CommentContract::KEY_ID];

            if ($headerId !== $id) {
                $this->headers[CommentContract::KEY_ID] = $id;
                $this->rawHeaders[CommentContract::KEY_ID] = $id;
            }
        }
    }

    /**
     * Checks the resolved data values and fills any supplemental data, if required.
     * @param string $path The comment's storage path.
     */
    private function fillSupplementalDataIfRequired($path)
    {
        if (array_key_exists(CommentContract::KEY_EMAIL, $this->headers) === false) {
            $this->headers[CommentContract::INTERNAL_HAS_SUPPLEMENTED_DATA] = true;
            $this->headers[CommentContract::INTERNAL_PARSER_AUTHOR_EMAIL_SUPPLEMENTED] = true;
            $this->headers[CommentContract::KEY_EMAIL] = $this->config->supplementAuthorEmail;
            $this->rawHeaders[CommentContract::KEY_EMAIL] = $this->config->supplementAuthorEmail;
        }

        if (array_key_exists(CommentContract::KEY_NAME, $this->headers) === false) {
            $this->headers[CommentContract::INTERNAL_HAS_SUPPLEMENTED_DATA] = true;
            $this->headers[CommentContract::INTERNAL_PARSER_AUTHOR_NAME_SUPPLEMENTED] = true;
            $this->headers[CommentContract::KEY_NAME] = $this->config->supplementAuthorName;
            $this->rawHeaders[CommentContract::KEY_NAME] = $this->config->supplementAuthorName;
        }

        if (mb_strlen($this->content) === 0) {
            $this->headers[CommentContract::INTERNAL_HAS_SUPPLEMENTED_DATA] = true;
            $this->failedNodes[] = $this->getIdFromPath($path);
            $this->headers[CommentContract::INTERNAL_PARSER_CONTENT_SUPPLEMENTED] = true;
            $this->rawHeaders[CommentContract::INTERNAL_PARSER_CONTENT_SUPPLEMENTED] = true;
            $this->content = $this->config->supplementMissingContent;
        }
    }

    /**
     * Resets the internal failed nodes collection.
     */
    public function resetFailedNodes()
    {
        $this->failedNodes = [];
    }

}
