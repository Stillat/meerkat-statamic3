<?php

namespace Stillat\Meerkat\Core\Comments;

/**
 * Class AffectsCommentsResult
 *
 * Represents an operation result that affects multiple comments.
 *
 * @since 2.0.0
 */
class AffectsCommentsResult
{
    /**
     * Whether the operation completed successfully.
     *
     * @var bool
     */
    public $success = false;

    /**
     * The comment identifiers affected by the action.
     *
     * @var array
     */
    public $comments = [];

    /**
     * Returns a general "failed" result.
     *
     * @return AffectsCommentsResult
     */
    public static function failed()
    {
        return new AffectsCommentsResult();
    }

    /**
     * Creates a new result based on the provided conditions.
     *
     * @param  bool  $success Whether the operation was a success.
     * @param  string[]  $comments The comment identifiers affected, if any.
     * @return AffectsCommentsResult
     */
    public static function conditionalWithComments($success, $comments)
    {
        $result = new AffectsCommentsResult();
        $result->success = $success;
        $result->comments = $comments;

        return $result;
    }

    /**
     * Creates a new success result with the provided comments.
     *
     * @param  string[]  $comments The comment identifiers affected, if any.
     * @return AffectsCommentsResult
     */
    public static function successWithComments($comments)
    {
        return self::conditionalWithComments(true, $comments);
    }

    /**
     * Creates a new failed result with the provided comments.
     *
     * @param  string[]  $comments The comment identifiers affected, if any.
     * @return AffectsCommentsResult
     */
    public static function failedWithComments($comments)
    {
        return self::conditionalWithComments(false, $comments);
    }
}
