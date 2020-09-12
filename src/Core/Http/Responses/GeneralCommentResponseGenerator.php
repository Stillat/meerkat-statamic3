<?php

namespace Stillat\Meerkat\Core\Http\Responses;

use Stillat\Meerkat\Core\Errors;

class GeneralCommentResponseGenerator
{
    const KEY_COMMENT_ID = 'comment_id';

    public function notFound($commentId)
    {
        return array_merge(Responses::fromErrorCode(Errors::COMMENT_NOT_FOUND, true), [
            self::KEY_COMMENT_ID => $commentId
        ]);
    }

}
