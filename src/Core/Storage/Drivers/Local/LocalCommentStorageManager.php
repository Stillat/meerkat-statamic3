<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use DateTime;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\InternalAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\PrototypeAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Attributes\TruthyAttributes;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Indexing\ShadowIndex;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Core\ValidationResult;
use Stillat\Meerkat\Core\Validators\PathPrivilegeValidator;

class LocalCommentStorageManager implements CommentStorageManagerContract
{

    const PATH_REPLIES_DIRECTORY = 'replies';

    /**
     * The Meerkat configuration instance.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The path where all comment threads are stored.
     *
     * @var string
     */
    protected $storagePath = '';
    /**
     * @var Paths|null
     */
    protected $paths = null;
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

    /**
     * A cache of thread structures.
     *
     * @var array
     */
    private $threadStructureCache = [];

    /**
     * A collection of storage directory validation results.
     *
     * @var ValidationResult
     */
    private $validationResults;

    /**
     * A list of internal attributes to scan when building comment prototypes.
     *
     * @var array
     */
    private $prototypeElements = [];

    /**
     * A list of internal "truth" attributes.
     * @var array
     */
    private $truthyPrototypeElements = [];

    /**
     * A list of internal attributes.
     *
     * @var array
     */
    private $internalElements = [];

    /**
     * The comment structure resolver instance.
     *
     * @var StructureResolverInterface
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

    /**
     * The comment index instance.
     *
     * @var ShadowIndex
     */
    private $commentShadowIndex = null;

    /**
     * The author retriever instance.
     *
     * @var CommentAuthorRetriever
     */
    private $authorRetriever = null;

    public function __construct(
        Configuration $config,
        YAMLParserContract $yamlParser,
        MarkdownParserContract $markdownParser,
        CommentAuthorRetriever $authorRetriever)
    {
        $this->commentShadowIndex = new ShadowIndex($config);
        $this->commentStructureResolver = new LocalCommentStructureResolver();
        $this->authorRetriever = $authorRetriever;
        $this->config = $config;
        $this->paths = new Paths($this->config);

        // Quick alias for less typing.
        $this->storagePath = PathUtilities::normalize($this->config->storageDirectory);

        $this->prototypeElements = PrototypeAttributes::getPrototypeAttributes();
        $this->internalElements = InternalAttributes::getInternalAttributes();
        $this->truthyPrototypeElements = TruthyAttributes::getTruthyAttributes();

        $this->commentShadowIndex->setIsCommentProtoTypeIndexEnabled(false);
        $this->commentShadowIndex->setIsThreadIndexEnabled(false);

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

    /**
     * Gets all comments for the requested thread.
     *
     * @param string $threadId The identifier of the thread.
     * @return array|mixed
     */
    public function getCommentsForThreadId($threadId)
    {
        if ($this->canUseDirectory === false) {
            return [];
        }

        if ($this->commentShadowIndex->hasProtoTypeIndex($threadId)) {
            return $this->commentShadowIndex->getProtoTypeIndex($threadId);
        }

        $threadPath = $this->paths->combine([$this->storagePath, $threadId]);

        $commentPaths = [];
        $commentPrototypes = [];

        if ($this->commentShadowIndex->hasIndex($threadId) === false) {
            $threadFilter = $this->paths->combine([$threadPath, '*comment.md']);
            $commentPaths = $this->paths->getFilesRecursively($threadFilter);

            $this->commentShadowIndex->buildIndex($threadId, $commentPaths);
        } else {
            $commentPaths = $this->commentShadowIndex->getThreadIndex($threadId);
        }

        // Build up statistics for the located comments.
        $this->commentStructureResolver->resolve($threadPath, $commentPaths);

        for ($i = 0; $i < count($commentPaths); $i += 1) {
            // First, let's get the "prototype" form of this comment.
            $commentInternalPath = $this->paths->normalize($commentPaths[$i]);
            $commentPrototype = $this->getCommentPrototype($commentInternalPath);

            if (count($commentPrototype['headers']) == 0) {
                continue;
            }

            if (array_key_exists(CommentContract::KEY_ID, $commentPrototype['headers']) === false) {
                continue;
            }

            $commentId = $commentPrototype['headers'][CommentContract::KEY_ID];
            $commentId = ltrim($commentId, '"\'');
            $commentId = rtrim($commentId, '"\'');

            $commentPrototype['headers'][CommentContract::INTERNAL_PATH] = $commentInternalPath;

            $comment = new Comment();

            // Start: Comment Implementation Specifics (not contract).
            $comment->setStorageManager($this);
            $comment->setAuthorRetriever($this->authorRetriever);
            // End:   Comment Implementation Specifics

            $comment->setDataAttributes($commentPrototype['headers']);
            $comment->setRawAttributes($commentPrototype['raw_headers']);
            $comment->setRawContent($commentPrototype['content']);
            $comment->setYamlParser($this->yamlParser);
            $comment->setMarkdownParser($this->markdownParser);

            if ($commentPrototype['needs_content_migration']) {
                $comment->setDataAttribute(CommentContract::INTERNAL_STRUCTURE_NEEDS_MIGRATION, true);
            }

            $commentPrototypes[$commentId] = $comment;
        }

        $dateFormatToUse = $this->config->getFormattingConfiguration()->commentDateFormat;

        /**
         * @var string $commentId
         * @var CommentContract $comment
         */
        foreach ($commentPrototypes as $commentId => $comment) {
            $hasAncestor = $this->commentStructureResolver->hasAncestor($commentId);
            $directChildren = $this->commentStructureResolver->getDirectDescendents($commentId);
            $allDescendents = $this->commentStructureResolver->getAllDescendents($commentId);
            $children = [];

            $isParent = count($directChildren) > 0;

            $commentDate = new DateTime();
            $commentDate->setTimestamp($commentId);

            $commentDateFormatted = $commentDate->format($dateFormatToUse);

            $comment->setDataAttribute(CommentContract::KEY_COMMENT_DATE, $commentDate);
            $comment->setDataAttribute(CommentContract::KEY_COMMENT_DATE_FORMATTED, $commentDateFormatted);
            $comment->setDataAttribute(CommentContract::KEY_IS_ROOT, !$hasAncestor);
            $comment->setDataAttribute(CommentContract::KEY_IS_PARENT, $isParent);
            $comment->setDataAttribute(CommentContract::KEY_DESCENDENTS, $allDescendents);

            if (count($allDescendents) == 0) {
                $comment->setDataAttribute(CommentContract::INTERNAL_ABSOLUTE_ROOT, $commentId);
            } else {
                $comment->setDataAttribute(CommentContract::INTERNAL_ABSOLUTE_ROOT, $allDescendents[0]);
            }

            $comment->setDataAttribute(
                CommentContract::KEY_DEPTH,
                $this->commentStructureResolver->getDepth($commentId)
            );

            $comment->setDataAttribute(
                CommentContract::KEY_ANCESTORS,
                $this->commentStructureResolver->getAllAncestors($commentId)
            );

            if ($isParent) {
                foreach ($directChildren as $child) {
                    if (array_key_exists($child, $commentPrototypes)) {
                        $children[] = $commentPrototypes[$child];
                    }
                }
            }

            if ($hasAncestor) {
                $commentParent = $this->commentStructureResolver->getParent($commentId);
                $comment->setDataAttribute(CommentContract::KEY_PARENT, $commentPrototypes[$commentParent]);
            }

            $comment->setDataAttribute(CommentContract::KEY_CHILDREN, $children);
            $comment->setDataAttribute(CommentContract::KEY_IS_REPLY, $hasAncestor);
            $comment->setReplies($children);

        }

        $this->commentShadowIndex->buildProtoTypeIndex($threadId, $commentPrototypes);

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
                            $headers[$protoParts[0]] = $this->cleanAttributeValue($protoParts[1]);

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

    public function isChildOf($child, $testParent)
    {
        // TODO: Implement isChildOf() method.
    }

    public function isParentOf($parent, $testChild)
    {
        // TODO: Implement isParentOf() method.
    }

    /**
     * Attempts to save the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return false
     */
    public function save(CommentContract $comment)
    {
        $storableAttributes = $comment->getStorableAttributes();

        foreach ($this->internalElements as $attribute) {
            if (array_key_exists($attribute, $storableAttributes)) {
                unset($storableAttributes[$attribute]);
            }
        }

        $contentToSave = $this->yamlParser->toYaml($storableAttributes, $comment->getRawContent());

        return file_put_contents($comment->getVirtualPath(), $contentToSave);
    }

    public function update(CommentContract $comment)
    {
        // TODO: Implement update() method.
    }
}