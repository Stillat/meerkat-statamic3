<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Comments\TransientCommentAttributes;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Core\ValidationResult;
use Stillat\Meerkat\Core\Validators\PathPrivilegeValidator;

class LocalCommentStorageManager implements CommentStorageManagerContract
{

    const PATH_REPLIES_DIRECTORY = 'replies';

    protected $config = null;

    protected $storagePath = '';

    /**
     * Indicates if the configured storage directory was validated.
     *
     * @var bool
     */
    private $directoryValidated = false;

    /**
     * Indicates if the configured storage directory is usable.
     *
     * @var bool
     */
    private $canUseDirectory = false;

    private $threadStructureCache = [];

    /**
     * A collection of storage directory validation results.
     *
     * @var ValidationResult
     */
    private $validationResults;

    private $prototypeElements = [
        'name', 'email', 'id', 'user_ip',
        'published', 'user_agent', 'referrer',
        'page_url', 'spam', 'authenticated_user',
    ];

    private $truthyPrototypeElements = [
        'published', 'spam'
    ];

    /**
     * @var Paths|null
     */
    protected $paths = null;

    /**
     * The comment structure resolver instance.
     *
     * @var LocalCommentStructureResolver
     */
    private $commentStructureResolver = null;

    /**
     * The YAML parser implementation instance.
     *
     * @var YAMLParserContract
     */
    private $yamlParser = null;

    /**
     * The Markdown parser implementation instance.
     *
     * @var MarkdownParserContract
     */
    private $markdownParser = null;

    public function __construct(
        Configuration $config,
        YAMLParserContract $yamlParser,
        MarkdownParserContract $markdownParser)
    {
        $this->commentStructureResolver = new LocalCommentStructureResolver();
        $this->config = $config;
        $this->paths = new Paths($this->config);
        // Quick alias for less typing.
        $this->storagePath = PathUtilities::normalize($this->config->storageDirectory);

        $this->yamlParser = $yamlParser;
        $this->markdownParser = $markdownParser;

        $this->validationResults = new ValidationResult();
        $this->validate();
    }

    public function validate()
    {
        if ($this->directoryValidated) {
            return $this->validationResults;
        }

        $results = PathPrivilegeValidator::validatePathPermissions(
            $this->storagePath,
            Errors::DRIVER_LOCAL_INSUFFICIENT_PRIVILEGES
        );

        $this->validationResults = $results[PathPrivilegeValidator::RESULT_VALIDATION_RESULTS];
        $this->canUseDirectory = $results[PathPrivilegeValidator::RESULT_CAN_USE_DIRECTORY];

        $this->validationResults->updateValidity();
        $this->directoryValidated = true;

        return $this->validationResults;
    }

    public function getCommentsForThreadId($threadId)
    {
        $threadPath = $this->paths->combine([$this->storagePath, $threadId]);
        $threadFilter = $this->paths->combine([$threadPath, '*comment.md']);
        $commentPaths = $this->paths->getFilesRecursively($threadFilter);
        $commentPrototypes = [];

        // Build up statistics for the located comments.
        $this->commentStructureResolver->resolve($threadPath, $commentPaths);

        for ($i = 0; $i < count($commentPaths); $i += 1) {
            // First, let's get the "prototype" form of this comment.
            $commentInternalPath = $this->paths->normalize($commentPaths[$i]);
            $commentPrototype = $this->getCommentPrototype($commentInternalPath);

            if (count($commentPrototype['headers']) == 0) {
                continue;
            }

            $commentPrototype['headers'][CommentContract::INTERNAL_PATH] = $commentInternalPath;

            $comment = new Comment();
            $comment->setDataAttributes($commentPrototype['headers']);
            $comment->setRawAttributes($commentPrototype['raw_headers']);
            $comment->setRawContent($commentPrototype['content']);
            $comment->setYamlParser($this->yamlParser);
            $comment->setMarkdownParser($this->markdownParser);

            if ($commentPrototype['needs_content_migration']) {
                $comment->setDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, true);
            }

            $commentPrototypes[] = $comment;
        }

        return $commentPrototypes;
    }

    /**
     * Retrieves only the core meta-data for the comment.
     *
     * Supplemental data and content are ignored during this phase.
     *
     * @param string $path The full path to the comment data.
     * @return array
     */
    private function getCommentPrototype($path)
    {
        $handle = fopen($path, 'r');
        $headerDelimiterObserved = 0;
        $headers = [];
        $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = false;

        $rawHeaders = [];
        $collectHeaders = true;
        $content = '';
        $contentLine = -1;
        $alreadyFoundContent = false;

        if ($handle) {

            while (($line = fgets($handle)) !== false) {
                $trimLine = trim($line);
                $doProcessHeaders = true;

                if ($trimLine === '---') {
                    $headerDelimiterObserved += 1;
                    $doProcessHeaders = false;
                }

                if ($headerDelimiterObserved >= 2) {
                    $collectHeaders = false;
                    $doProcessHeaders = false;
                }

                if ($doProcessHeaders) {
                    if ($collectHeaders) {
                        $rawHeaders[] = $line;
                    }

                    $protoParts = explode(': ', $trimLine, 2);

                    if (is_array($protoParts) == true && count($protoParts) == 2) {
                        if ($protoParts[0] == 'comment') {
                            if (mb_strlen($protoParts[1]) > $this->config->hardCommentLengthCap) {
                                $content = mb_substr($protoParts[1], 0, $this->config->hardCommentLengthCap);
                                $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                            } else {
                                $content = $protoParts[1];
                            }

                            $alreadyFoundContent = true;
                        }

                        if (in_array($protoParts[0], $this->prototypeElements)) {
                            $headers[$protoParts[0]] = $protoParts[1];

                            if (in_array($protoParts[0], $this->truthyPrototypeElements)) {
                                $headers[$protoParts[0]] = TypeConversions::getBooleanValue($protoParts[1]);
                            }
                        }
                    }
                }

                if ($doProcessHeaders == false && $collectHeaders == false && $alreadyFoundContent == false) {
                    $contentLine += 1;

                    if ($contentLine > 0) {
                        $content .= $line;

                        if (mb_strlen($content) > $this->config->hardCommentLengthCap) {
                            $content = mb_substr($content, 0, $this->config->hardCommentLengthCap);
                            $headers[CommentContract::INTERNAL_CONTENT_TRUNCATED] = true;
                            break;
                        }
                    }
                }
            }

            fclose($handle);
        }

        return [
            'headers' => $headers,
            'raw_headers' => $rawHeaders,
            'content' => $content,
            'needs_content_migration' => $alreadyFoundContent
        ];
    }

    public function isChildOf($child, $testParent)
    {
        // TODO: Implement isChildOf() method.
    }

    public function isParentOf($parent, $testChild)
    {
        // TODO: Implement isParentOf() method.
    }
}