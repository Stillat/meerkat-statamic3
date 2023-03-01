<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use DateTime;
use Serializable;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\ParsesMarkdownContract;
use Stillat\Meerkat\Core\Contracts\Parsing\ParsesYamlContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSetCollection;

/**
 * Interface CommentContract
 *
 * Defines a standard structure for Meerkat comments
 *
 * @since 2.0.0
 */
interface CommentContract extends DataObjectContract, Serializable, ParsesMarkdownContract, ParsesYamlContract
{
    const COMMENT_FILENAME = 'comment.md';

    const KEY_REPLIES = 'replies';

    const KEY_COMMENT_DATE_FORMATTED = 'comment_date_formatted';

    const KEY_CONTENT = 'content';

    const KEY_LEGACY_COMMENT = 'comment';

    const KEY_COMMENT_MARKDOWN = 'comment_markdown';

    const KEY_ID = 'id';

    const KEY_COMMENT_DATE = 'comment_date';

    const KEY_ALIAS_DATE = 'date';

    const KEY_ALIAS_DATE_FORMATTED = 'date_formatted';

    const KEY_IS_REPLY = 'is_reply';

    const KEY_DEPTH = 'depth';

    const KEY_ANCESTORS = 'ancestors';

    const KEY_DESCENDENTS = 'descendants';

    const KEY_CHILDREN = 'children';

    const KEY_PARENT = 'parent';

    const KEY_AUTHOR = 'author';

    const KEY_PARENT_ID = 'parent_id';

    const KEY_IS_PARENT = 'is_parent';

    const KEY_IS_ROOT = 'is_root';

    const KEY_IS_DELETED = 'is_deleted';

    const KEY_PUBLISHED = 'published';

    const KEY_SPAM = 'spam';

    const KEY_HAS_CHECKED_FOR_SPAM = 'has_checked_for_spam';

    const KEY_HAS_REPLIES = 'has_replies';

    const KEY_NAME = 'name';

    const KEY_EMAIL = 'email';

    const KEY_USER_IP = 'user_ip';

    const KEY_USER_AGENT = 'user_agent';

    const KEY_REFERRER = 'referer';

    const KEY_PAGE_URL = 'page_url';

    const INTERNAL_HAS_COLLECTED = 'internal_runtime_has_collected';

    const INTERNAL_PARENT_AUTHOR = 'internal_parent_author';

    const INTERNAL_ABSOLUTE_ROOT = 'internal_root';

    const INTERNAL_CONTENT_TRUNCATED = 'internal_content_truncated';

    const INTERNAL_CONTEXT = 'context';

    const INTERNAL_CONTENT_RAW = 'content_raw';

    const INTERNAL_PATH = 'internal_path';

    const INTERNAL_RESPONSE = 'internal_response';

    const INTERNAL_RESPONSE_PATH = 'internal_response_path';

    const INTERNAL_RESPONSE_ID = 'internal_response_id';

    const INTERNAL_RESPONSE_CONTEXT = 'internal_response_context';

    const INTERNAL_CONTEXT_ID = 'internal_context_id';

    const INTERNAL_RESPONSE_HAS_REPLIES = 'internal_response_has_replies';

    const INTERNAL_STRUCTURE_NEEDS_MIGRATION = 'internal_needs_structure_migration';

    const INTERNAL_HISTORY_REVISION_COUNT = 'revision_count';

    const INTERNAL_HAS_SUPPLEMENTED_DATA = 'parser_has_supplemented_data';

    const INTERNAL_PARSER_CONTENT_SUPPLEMENTED = 'parser_content_supplemented';

    const INTERNAL_PARSER_AUTHOR_NAME_SUPPLEMENTED = 'parser_author_name_supplemented';

    const INTERNAL_PARSER_AUTHOR_EMAIL_SUPPLEMENTED = 'parser_author_email_supplemented';

    const INTERNAL_AUTHOR_HAS_EMAIL = 'internal_author_has_email';

    const INTERNAL_AUTHOR_HAS_NAME = 'internal_author_has_name';

    const INTERNAL_STRUCTURE_HAS_REPLIES = 'has_replies';

    /**
     * Indicates if the comment was left by an authenticated user.
     *
     * @return bool
     */
    public function leftByAuthenticatedUser();

    /**
     * Sets if the comment is a new comment.
     *
     * @param  bool  $isNew Indicates if the comment is a "new" comment.
     * @return mixed
     */
    public function setIsNew($isNew);

    /**
     * Sets the thread's string identifier.
     *
     * @param  string  $threadId The thread string identifier.
     * @return void
     */
    public function setThreadId($threadId);

    /**
     * Gets the comment's thread string identifier.
     *
     * @return string|null
     */
    public function getThreadId();

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
     * @param  string  $content The content.
     * @return CommentContract
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
     * @param  string  $content The content.
     * @return CommentContract
     */
    public function setRawContent($content);

    /**
     * Gets the comments virtual storage path.
     *
     * @return string
     */
    public function getVirtualPath();

    /**
     * Sets the comments raw attribute values.
     *
     * @param  array  $attributes The attributes.
     * @return CommentContract
     */
    public function setRawAttributes($attributes);

    /**
     * Returns a value indicating if the comment has replies.
     *
     * @return bool
     */
    public function getHasReplies();

    /**
     * Gets the date/time the comment was submitted.
     *
     * @return DateTime
     */
    public function getCommentDate();

    /**
     * Gets the formatted date/time the comment was submitted.
     *
     * @return string
     */
    public function getFormattedCommentDate();

    /**
     * Sets a value indicating that the comment should always report it has replies.
     *
     * @return CommentContract
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
     * @param  CommentContract[]  $replies The replies to the comment.
     * @return CommentContract
     */
    public function setReplies($replies);

    /**
     * Marks the comment as unpublished.
     *
     * @return bool
     */
    public function unpublish();

    /**
     * Marks the comment as published.
     *
     * @return bool
     */
    public function publish();

    /**
     * Returns a value indicating if the comment is published.
     *
     * @return bool
     */
    public function published();

    /**
     * Returns a value indicating if the comment is a root level comment.
     *
     * @return bool
     */
    public function isRoot();

    /**
     * Returns the ID of the comment's absolute root.
     *
     * @return string
     */
    public function getRoot();

    /**
     * Gets the comments depth in the reply hierarchy.
     *
     * @return int
     */
    public function getDepth();

    /**
     * Tests if the comment has been checked for spam.
     *
     * @return bool
     */
    public function hasBeenCheckedForSpam();

    /**
     * Returns a value indicating if the comment was marked as spam.
     *
     * @return bool
     */
    public function isSpam();

    /**
     * Returns a value indicating if the comment is a reply.
     *
     * @return bool
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
     * @param  CommentContract  $comment The parent comment.
     * @return CommentContract
     */
    public function setParentComment($comment);

    /**
     * Gets the identifier for the parent comment, if available.
     *
     * @return string
     */
    public function getParentId();

    /**
     * Tests if the comment is a parent comment.
     *
     * @return bool
     */
    public function isParent();

    /**
     * Gets the date the comment was created.
     *
     * @return string
     */
    public function getDate();

    /**
     * Gets whether or not the comment is deleted.
     *
     * @return bool
     */
    public function isDeleted();

    /**
     * Sets the comment's author context.
     *
     * @param  AuthorContract  $author The author of the comment.
     * @return CommentContract
     */
    public function setAuthor($author);

    /**
     * Gets the comment's author instance.
     *
     * @return AuthorContract
     */
    public function getAuthor();

    /**
     * Sets the comment's parent author context, if available.
     *
     * @param  AuthorContract  $author The author of the parent comment.
     * @return CommentContract
     */
    public function setParentAuthor($author);

    /**
     * Gets the comment's parent author instance, if available.
     *
     * @return AuthorContract|null
     */
    public function getParentAuthor();

    /**
     * Indicates if the comment's author has a name.
     *
     * @since 2.1.14
     *
     * @return bool
     */
    public function hasAuthorName();

    /**
     * Indicates if the comment's author has an email address.
     *
     * @since 2.1.14
     *
     * @return bool
     */
    public function hasAuthorEmailAddress();

    /**
     * Gets the comment's participants.
     *
     * @return AuthorContract[]
     */
    public function getParticipants();

    /**
     * Attempts to retrieve the participants for the thread.
     *
     * @return AuthorContract[]
     */
    public function getThreadParticipants();

    /**
     * Attempts to retrieve the participants for the thread that are not this comment's author.
     *
     * @return AuthorContract[]
     */
    public function getOtherThreadParticipants();

    /**
     * Attempts to locate the comment's thread context.
     *
     * @return ThreadContract|null
     */
    public function getThread();

    /**
     * Sets the comment's participants.
     *
     * @param  AuthorContract[]  $participants The comment's participants.
     * @return CommentContract
     */
    public function setParticipants($participants);

    /**
     * Gets the comment's storable data.
     *
     * @return array
     */
    public function getStorableAttributes();

    /**
     * Saves the comment's data.
     *
     * @return bool
     */
    public function save();

    /**
     * Indicates if the comment is a new instance, or one loaded from storage.
     *
     * @return bool
     */
    public function getIsNew();

    /**
     * Updates the comment's data structure and saves the comment.
     *
     * @return bool
     */
    public function updateStructure();

    /**
     * Updates the comment's content and saves the comment.
     *
     * @param  string  $content The new content.
     * @return bool
     */
    public function updateCommentContent($content);

    /**
     * Sets the string identifier of the parent comment.
     *
     * @param  string  $parentId The parent comment's identifier.
     * @return void
     */
    public function setParentId($parentId);

    /**
     * Converts the comment into an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns the revision count.
     *
     * @return int
     */
    public function getRevisionCount();

    /**
     * Returns the comment's change set collection.
     *
     * @return ChangeSetCollection|null
     */
    public function getRevisions();
}
