<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class IdRetriever
 *
 * Provides utilities to locate comments from a list of input fields.
 *
 * @since 2.0.0
 */
class IdRetriever
{
    const KEY_IDS = 'ids';

    const KEY_IS_REPLYING = 'is_reply';

    /**
     * Attempts to determine if the fields contains a comment to reply.
     *
     * If a shared instance has been supplied to CommentManagerFactory,
     * this method will validate the existence of the found comment.
     *
     * @param  array  $fields The fields to retrieve data for.
     * @return array
     */
    public static function getIdAndValidateExistence($fields)
    {
        $isReply = false;
        $replyingTo = null;

        if (isset($fields[self::KEY_IDS]) && ! empty($fields[self::KEY_IDS])) {
            if (is_array($fields[self::KEY_IDS]) && count($fields[self::KEY_IDS]) > 0) {
                $replyingTo = $fields[self::KEY_IDS][0];
            } else {
                $replyingTo = $fields[self::KEY_IDS];
            }

            if (mb_strlen(trim($replyingTo)) == 0) {
                $isReply = false;
            } else {
                // Attempt to locate the comment.
                if (CommentManagerFactory::hasInstance()) {
                    $commentFindResult = CommentManagerFactory::$instance->findById($replyingTo);

                    if ($commentFindResult === null) {
                        $isReply = false;
                        $replyingTo = null;
                    } else {
                        $isReply = true;
                        $replyingTo = $commentFindResult->getId();
                    }
                } else {
                    $isReply = true;
                }
            }
        }

        return [
            self::KEY_IS_REPLYING => $isReply,
            CommentContract::KEY_ID => $replyingTo,
        ];
    }
}
