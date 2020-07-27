<?php

namespace Stillat\Meerkat\Core\Comments;

use DateTime;
use Stillat\Meerkat\Core\Comments\StaticApi\ProvidesDiscovery;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\InconsistentCompositionException;
use Stillat\Meerkat\Core\Parsing\UsesMarkdownParser;
use Stillat\Meerkat\Core\Parsing\UsesYAMLParser;
use Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class Comment
 *
 * Provides a consistent base implementation for comments.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class Comment implements CommentContract
{
    use DataObject, UsesMarkdownParser, UsesYAMLParser, ProvidesDiscovery;

    /**
     * The comment's parent instance, if available.
     *
     * @var CommentContract
     */
    protected $commentParent = null;

    /**
     * The participants involved in this comment.
     *
     * @var AuthorContract[]
     */
    protected $commentParticipants = [];

    /**
     * The comment's author instance.
     *
     * @var AuthorContract
     */
    protected $commentAuthor = null;

    /**
     * Indicates if the always has comments override has been set.
     *
     * @var boolean
     */
    protected $commentOverrideAlwaysHasReplies = false;

    /**
     * The replies for this comment.
     *
     * @var CommentContract[]
     */
    protected $commentReplies = [];

    /**
     * A collection of data attributes for this comment.
     *
     * @var array The data attributes.
     */
    protected $attributes = [];

    /**
     * A collection of raw, unparsed data attributes.
     *
     * @var array
     */
    protected $rawAttributes = [];

    /**
     * The original raw content.
     *
     * @var string
     */
    protected $rawContent = '';

    /**
     * Indicates if the full set of run-time attributes has been resolved.
     *
     * @var bool
     */
    protected $runTimeAttributesResolved = false;

    /**
     * The storage manager instance.
     *
     * @var CommentStorageManagerContract|null
     */
    private $storageManager = null;

    /**
     * @var CommentAuthorRetriever|null
     */
    private $authorManager = null;

    private $isNew = false;

    private $threadId = null;


    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;
    }

    public function setStorageManager(&$manager)
    {
        $this->storageManager = $manager;
    }

    public function setAuthorRetriever(&$retriever)
    {
        $this->authorManager = $retriever;
    }

    /**
     * Returns the comment's content.
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->hasDataAttribute(CommentContract::KEY_CONTENT)) {
            return $this->getDataAttribute(CommentContract::KEY_CONTENT, '');
        }

        return $this->rawContent;
    }

    /**
     * Sets the comment's content.
     *
     * @param string $content
     * @return CommentContract
     *
     * @throws InconsistentCompositionException
     */
    public function setContent($content)
    {
        $this->getMarkdownParser()->parseStringAndMerge($content, $this->attributes);

        return $this;
    }

    /**
     * Gets the comment's raw content.
     *
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * Sets the comment's raw content value.
     *
     * @param string $content The content.
     * @return CommentContract
     */
    public function setRawContent($content)
    {
        $this->rawContent = $content;
        $this->setDataAttribute(CommentContract::INTERNAL_CONTENT_RAW, $content);

        return $this;
    }

    /**
     * Sets the comments raw attribute values.
     *
     * @param array $attributes The attributes.
     * @return CommentContract
     */
    public function setRawAttributes($attributes)
    {
        $this->rawAttributes = $attributes;

        return $this;
    }

    /**
     * Returns a value indicating if the comment has replies.
     *
     * @return boolean
     */
    public function getHasReplies()
    {
        if ($this->commentOverrideAlwaysHasReplies === true) {
            return true;
        }

        $children = TypeConversions::getArray($this->getDataAttribute(CommentContract::KEY_CHILDREN, []));

        return count($children) > 0;
    }

    /**
     * Sets a value indicating that the comment should always report it has replies.
     *
     * @return CommentContract
     */
    public function alwaysReportCommentHasReplies()
    {
        $this->commentOverrideAlwaysHasReplies = true;

        return $this;
    }

    /**
     * Marks the comment as unpublished.
     *
     * @return boolean
     */
    public function unpublish()
    {
        $this->setDataAttribute(CommentContract::KEY_PUBLISHED, false);

        return $this->save();
    }

    /**
     * Saves the comment's data.
     *
     * @return bool
     */
    public function save()
    {
        if ($this->storageManager === null) {
            return false;
        }

        return $this->storageManager->save($this);

        /*$attributes = $this->getAttributesToSave();
        $content = '';

        if (array_key_exists(CommentContract::KEY_CONTENT, $attributes)) {
            $content = $attributes[CommentContract::KEY_CONTENT];
            unset($attributes[CommentContract::KEY_CONTENT]);
        }

        $contentToSave = $this->yamlParser->toYaml($attributes, $content);
        $storagePath = $this->getStoragePath();

        if ($storagePath === null) {
            return false;
        }

        $saveResult = file_put_contents($storagePath, $contentToSave);

        if ($saveResult === false) {
            return false;
        }

        return true;*/
    }

    /**
     * Filters the run-time data attributes and returns only those that should be saved.
     *
     * @return array
     */
    private function getAttributesToSave()
    {
        $cleanableProperties = CleanableCommentAttributes::getCleanableAttributes();
        $transientProperties = TransientCommentAttributes::getTransientProperties();
        $attributes = $this->attributes;

        foreach ($transientProperties as $property) {
            if (array_key_exists($property, $attributes)) {
                unset($attributes[$property]);
            }
        }

        foreach ($attributes as $attributeName => $value) {
            if (in_array($attributeName, $cleanableProperties)) {
                $value = ltrim($value, '"\' ');
                $value = rtrim($value, '"\' ');

                $attributes[$attributeName] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Gets the internal storage path for this comment.
     *
     * @return string
     */
    private function getStoragePath()
    {
        return $this->getDataAttribute(CommentContract::INTERNAL_PATH, null);
    }

    /**
     * Marks the comment as published.
     *
     * @return boolean
     */
    public function publish()
    {
        $this->setDataAttribute(CommentContract::KEY_PUBLISHED, true);

        return $this->save();
    }

    /**
     * Returns a value indicating if the comment is published.
     *
     * @return boolean
     */
    public function published()
    {
        return TypeConversions::getBooleanValue(
            $this->getDataAttribute(CommentContract::KEY_PUBLISHED, false)
        );
    }

    /**
     * Returns a value indicating if the comment is a root level comment.
     *
     * @return boolean
     */
    public function isRoot()
    {
        return $this->getDataAttribute(CommentContract::KEY_IS_ROOT, true);
    }

    /**
     * Returns the ID of the comment's absolute root.
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->getDataAttribute(CommentContract::INTERNAL_ABSOLUTE_ROOT, null);
    }

    /**
     * Gets the comments depth in the reply hierarchy.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->getDataAttribute(CommentContract::KEY_DEPTH, 0);
    }

    /**
     * Returns a value indicating if the comment is a reply.
     *
     * @return boolean
     */
    public function isReply()
    {
        return TypeConversions::getBooleanValue(
            $this->getDataAttribute(CommentContract::KEY_IS_REPLY, false)
        );
    }

    /**
     * Gets the comment's replies.
     *
     * @return CommentContract[]
     */
    public function getReplies()
    {
        return $this->commentReplies;
    }

    /**
     * Sets the comment's replies.
     *
     * @param CommentContract[] $replies The replies to the comment.
     * @return CommentContract
     */
    public function setReplies($replies)
    {
        $this->commentReplies = $replies;

        return $this;
    }

    /**
     * Returns the parent comment instance, if any.
     *
     * @return CommentContract|null
     */
    public function getParentComment()
    {
        return $this->commentParent;
    }

    /**
     * Sets the parent comment for this comment instance.
     *
     * @param CommentContract $comment The parent comment.
     * @return CommentContract
     */
    public function setParentComment($comment)
    {
        $this->commentParent = $comment;

        return $this;
    }

    /**
     * Gets the identifier for the parent comment, if available.
     *
     * @return string
     */
    public function getParentId()
    {
        return $this->getDataAttribute(CommentContract::KEY_PARENT, null);
    }

    /**
     * Returns a value indicating if the comment was marked as spam.
     *
     * @return boolean
     */
    public function isSpam()
    {
        return TypeConversions::getBooleanValue($this->getDataAttribute(CommentContract::KEY_SPAM, false));
    }

    /**
     * Gets the date the comment was created.
     *
     * @return string
     */
    public function getDate()
    {
        // The date/time the comment was created is coded as the ID.
        return $this->getId();
    }

    /**
     * Returns the identifier for the comment.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getDataAttribute(CommentContract::KEY_ID);
    }

    /**
     * Gets whether or not the comment is deleted.
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->getDataAttribute(CommentContract::KEY_IS_DELETED, false);
    }

    /**
     * Gets the comment's participants.
     *
     * @return AuthorContract[]
     */
    public function getParticipants()
    {
        return $this->commentParticipants;
    }

    /**
     * Sets the comment's participants.
     *
     * @param AuthorContract[] $participants The comment's participants.
     * @return CommentContract
     */
    public function setParticipants($participants)
    {
        $this->commentParticipants = $participants;

        return $this;
    }

    /**
     * Sets the comment's author context.
     *
     * @param AuthorContract $author The author of the comment.
     * @return CommentContract
     */
    public function setAuthor($author)
    {
        $this->commentAuthor = $author;

        return $this;
    }

    /**
     * Gets the comment's author instance.
     *
     * @return AuthorContract
     */
    public function getAuthor()
    {
        if ($this->commentAuthor === null && $this->authorManager !== null) {
            $this->commentAuthor = $this->authorManager->getCommentAuthor($this);
        }

        return $this->commentAuthor;
    }

    /**
     * Gets the comment's storable data.
     *
     * @return array
     */
    public function getStorableAttributes()
    {
        $this->resolveRunTimeAttributes();

        return $this->getAttributesToSave();
    }

    /**
     * Parses the raw attributes and reloads the current attributes.
     *
     * Note: This method incurs a performance penalty, and should only be used when necessary!
     *
     * @return void
     */
    protected function resolveRunTimeAttributes()
    {
        if (count($this->rawAttributes) == 0) {
            return;
        }

        if ($this->runTimeAttributesResolved) {
            return;
        }

        $this->yamlParser->parseAndMerge(join('', $this->rawAttributes), $this->attributes);

        $this->runTimeAttributesResolved = true;
    }

    /**
     * Indicates if the comment is a new instance, or one loaded from storage.
     *
     * @return bool
     */
    public function getIsNew()
    {
        if ($this->isNew === true) {
            return true;
        }

        $virtualPath = $this->getVirtualPath();

        if ($virtualPath === null) {
            return true;
        }

        return !file_exists($virtualPath);
    }

    /**
     * Gets the comments virtual storage path.
     *
     * @return string
     */
    public function getVirtualPath()
    {
        if ($this->isNew) {
            return $this->storageManager->generateVirtualPath($this->threadId, $this->getId());
        }

        return $this->getDataAttribute(CommentContract::INTERNAL_PATH);
    }

    /**
     * Updates the comment's data structure and saves the comment.
     *
     * @return bool
     */
    public function updateStructure()
    {
        if ($this->storageManager === null) {
            return false;
        }

        $this->resolveRunTimeAttributes();

        if ($this->hasDataAttribute(CommentContract::KEY_LEGACY_COMMENT)) {
            $this->reassignDataProperty(
                CommentContract::KEY_LEGACY_COMMENT,
                CommentContract::KEY_CONTENT
            );
            $this->setRawContent($this->getDataAttribute(CommentContract::KEY_CONTENT));
        }

        return $this->save();
    }

    /**
     * Gets the date/time the comment was submitted.
     *
     * @return DateTime
     */
    public function getCommentDate()
    {
        return $this->getDataAttribute(CommentContract::KEY_COMMENT_DATE);
    }

    /**
     * Gets the formatted date/time the comment was submitted.
     *
     * @return string
     */
    public function getFormattedCommentDate()
    {
        return $this->getDataAttribute(CommentContract::KEY_COMMENT_DATE_FORMATTED);
    }

    /**
     * Updates the comment's content and saves the comment.
     *
     * @param string $content The new content.
     * @return bool
     */
    public function updateCommentContent($content)
    {
        if ($this->storageManager === null) {
            return false;
        }

        $this->setRawContent($content);

        return $this->save();
    }

    public function toArray()
    {
        $data = $this->getDataAttributes();

        $data[CommentContract::KEY_CONTENT] = $data[CommentContract::INTERNAL_CONTENT_RAW];

        return $data;
    }

    public function __toString()
    {
        return $this->getId();
    }

}
