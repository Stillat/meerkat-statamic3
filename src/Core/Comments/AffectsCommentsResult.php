<?php

namespace Stillat\Meerkat\Core\Comments;

class AffectsCommentsResult
{

    public $success = false;

    public $comments = [];

    /**
     * @return AffectsCommentsResult
     */
    public static function failed()
    {
        return new AffectsCommentsResult();
    }

    /**
     * @param $success
     * @param $comments
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
     * @param $comments
     * @return AffectsCommentsResult
     */
    public static function successWithComments($comments)
    {
        return self::conditionalWithComments(true, $comments);
    }

    /**
     * @param $comments
     * @return AffectsCommentsResult
     */
    public static function failedWithComments($comments)
    {
        return self::conditionalWithComments(false, $comments);
    }

}
