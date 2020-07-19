<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use Serializable;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;

/**
 * Interface CommentContract
 *
 * Defines a standard structure for Meerkat comments
 *
 * @package Stillat\Meerkat\Core\Contracts\Comments
 * @since 2.0.0
 */
interface CommentContract extends DataObjectContract, Serializable
{
    const COMMENT_FILENAME = 'comment.md';
    
    const KEY_REPLIES = 'replies';
    const KEY_COMMENT_DATE_FORMATTED = 'comment_date_formatted';
    const KEY_CONTENT = 'content';
    const KEY_COMMENT_MARKDOWN = 'comment_markdown';
    const KEY_ID = 'id';
    const KEY_COMMENT_DATE = 'comment_date';
    const KEY_IS_REPLY = 'isReply';
    const KEY_DEPTH = 'depth';
    const KEY_ANCESTORS = 'ancestors';
    const KEY_CHILDREN = 'children';
    const KEY_PARENT = 'parent';
    const KEY_IS_PARENT = 'is_parent';
    const KEY_IS_DELETED = 'is_deleted';
    const KEY_PUBLISHED = 'published';
    const KEY_SPAM = 'spam';

    const INTERNAL_CONTENT_TRUNCATED = 'internal_content_truncated';
    const INTERNAL_CONTEXT = 'context';
    const INTERNAL_CONTENT_RAW = 'content_raw';
    const INTERNAL_PATH = 'internal_path';
    const INTERNAL_RESPONSE = 'internal_response';
    const INTERNAL_RESPONSE_PATH = 'internal_response_path';
    const INTERNAL_RESPONSE_ID = 'internal_response_id';
    const INTERNAL_RESPONSE_CONTEXT = 'internal_response_context';
    const INTERNAL_RESPONSE_HAS_REPLIES = 'internal_response_has_replies';

    /**
     * Returns the identifier for the comment.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the comment's content.
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets the comment's content.
     *
     * @param  string $content The content.
     * @return void
     */
    public function setContent($content);

    /**
     * Gets the comment's raw content.
     *
     * @return string
     */
    public function getRawContent();

    /**
     * Sets the comment's raw content value.
     *
     * @param string $content The content.
     * @return void
     */
    public function setRawContent($content);

    /**
     * Sets the comments raw attribute values.
     *
     * @param array $attributes The attributes.
     * @return mixed
     */
    public function setRawAttributes($attributes);

    /**
     * Returns a value indicating if the comment has replies.
     *
     * @return boolean
     */
    public function getHasReplies();

    /**
     * Sets a value indicating that the comment should always report it has replies.
     *
     * @return void
     */
    public function alwaysReportCommentHasReplies();

    /**
     * Gets the comment's replies.
     *
     * @return CommentContract[]
     */
    public function getReplies();

    /**
     * Sets the comment's replies.
     *
     * @param  CommentContract[] $replies The replies to the comment.
     * @return void
     */
    public function setReplies($replies);

    /**
     * Marks the comment as unpublished.
     *
     * @return boolean
     */
    public function unpublish();

    /**
     * Marks the comment as published.
     *
     * @return boolean
     */
    public function publish();

    /**
     * Returns a value indicating if the comment is published.
     *
     * @return boolean
     */
    public function published();

    /**
     * Returns a value indicating if the comment is a root level comment.
     *
     * @return boolean
     */
    public function isRoot();

    /**
     * Returns a value indicating if the comment was marked as spam.
     *
     * @return boolean
     */
    public function isSpam();

    /**
     * Returns a value indicating if the comment is a reply.
     *
     * @return boolean
     */
    public function isReply();

    /**
     * Returns the parent comment instance, if any.
     *
     * @return CommentContract|null
     */
    public function getParentComment();

    /**
     * Sets the parent comment for this comment instance.
     *
     * @param  CommentContract $comment The parent comment.
     * @return void
     */
    public function setParentComment($comment);

    /**
     * Gets the identifier for the parent comment, if available.
     *
     * @return string
     */
    public function getParentId();

    /**
     * Gets the date the comment was created.
     *
     * @return string
     */
    public function getDate();

    /**
     * Gets whether or not the comment is deleted.
     *
     * @return boolean
     */
    public function isDeleted();

    /**
     * Sets the comment's author context.
     *
     * @param  AuthorContract $author The author of the comment.
     * @return void
     */
    public function setAuthor($author);

    /**
     * Gets the comment's author instance.
     *
     * @return AuthorContract
     */
    public function getAuthor();

    /**
     * Gets the comment's participants.
     *
     * @return AuthorContract[]
     */
    public function getParticipants();

    /**
     * Sets the comment's participants.
     *
     * @param  AuthorContract[] $participants The comment's participants.
     * @return void
     */
    public function setParticipants($participants);

}
