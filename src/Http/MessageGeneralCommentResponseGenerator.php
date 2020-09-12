<?php

namespace Stillat\Meerkat\Http;

use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Http\Responses\GeneralCommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;

class MessageGeneralCommentResponseGenerator extends GeneralCommentResponseGenerator
{
    use UsesTranslations;

    public function notFound($commentId)
    {
        return $this->mergeWithMessage(parent::notFound($commentId));
    }

    private function mergeWithMessage($result)
    {
        if (array_key_exists(Responses::KEY_ERROR_CODE, $result)) {
            $result[Responses::KEY_MESSAGE] = $this->translateErrorCode($result[Responses::KEY_ERROR_CODE], $result);
        }

        return $result;
    }

}
