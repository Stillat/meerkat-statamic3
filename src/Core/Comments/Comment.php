<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\InconsistentCompositionException;
use Stillat\Meerkat\Core\Parsing\UsesMarkdownParser;
use Stillat\Meerkat\Core\Parsing\UsesYAMLParser;
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
    use DataObject, UsesMarkdownParser, UsesYAMLParser;

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
     * @return void
     *
     * @throws InconsistentCompositionException
     */
    public function setContent($content)
    {
        $this->getMarkdownParser()->parseStringAndMerge($content, $this->attributes);

        throw InconsistentCompositionException::make('attributes', __CLASS__);
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
     * @return void
     */
    public function setRawContent($content)
    {
        $this->rawContent = $content;
        $this->setDataAttribute(CommentContract::INTERNAL_CONTENT_RAW, $content);
    }

    /**
     * Sets the comments raw attribute values.
     *
     * @param array $attributes The attributes.
     * @return mixed
     */
    public function setRawAttributes($attributes)
    {
        $this->rawAttributes = $attributes;
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
     * @return void
     */
    public function alwaysReportCommentHasReplies()
    {
        $this->commentOverrideAlwaysHasReplies = true;
    }

    /**
     * Marks the comment as unpublished.
     *
     * @return boolean
     */
    public function unpublish()
    {
        $this->setDataAttribute(CommentContract::KEY_PUBLISHED, false);

        return true;
    }

    /**
     * Marks the comment as published.
     *
     * @return boolean
     */
    public function publish()
    {
        $this->setDataAttribute(CommentContract::KEY_PUBLISHED, true);

        return true;
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
        return !$this->isReply();
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
     * @return void
     */
    public function setReplies($replies)
    {
        $this->commentReplies = $replies;
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
     * @return void
     */
    public function setParentComment($comment)
    {
        $this->commentParent = $comment;
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
     * @return void
     */
    public function setParticipants($participants)
    {
        $this->commentParticipants = $participants;
    }

    /**
     * Sets the comment's author context.
     *
     * @param AuthorContract $author The author of the comment.
     * @return void
     */
    public function setAuthor($author)
    {
        $this->commentAuthor = $author;
    }

    /**
     * Gets the comment's author instance.
     *
     * @return AuthorContract
     */
    public function getAuthor()
    {
        return $this->commentAuthor;
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
     * Gets the internal storage path for this comment.
     *
     * @return string
     */
    private function getStoragePath()
    {
        return $this->getDataAttribute(CommentContract::INTERNAL_PATH, null);
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
     * Saves the comment's data.
     *
     * @return bool
     */
    public function save()
    {
        $attributes = $this->getAttributesToSave();
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

        return true;
    }

    /**
     * Updates the comment's data structure and saves the comment.
     *
     * @return bool
     */
    public function updateStructure()
    {
        $this->resolveRunTimeAttributes();

        if ($this->hasDataAttribute(CommentContract::KEY_LEGACY_COMMENT)) {
            $this->reassignDataProperty(
                CommentContract::KEY_LEGACY_COMMENT,
                CommentContract::KEY_CONTENT
            );
        }

        return $this->save();
    }

    public function __toString()
    {
        return $this->getId();
    }

}
