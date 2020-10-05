<?php

namespace Stillat\Meerkat\Http;

use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Http\Responses\GeneralCommentResponseGenerator;
use Stillat\Meerkat\Core\Http\Responses\Responses;

/**
 * Class MessageGeneralCommentResponseGenerator
 *
 * Provides helpers for generating standardized response messages.
 *
 * @package Stillat\Meerkat\Http
 * @since 2.0.0
 */
class MessageGeneralCommentResponseGenerator extends GeneralCommentResponseGenerator
{
    use UsesTranslations;

    /**
     * Generates a comment not found response.
     *
     * @param string $commentId The comment identifier.
     * @return array
     */
    public function notFound($commentId)
    {
        return $this->mergeWithMessage(parent::notFound($commentId));
    }

    /**
     * Merges the results with any error code translation messages.
     *
     * @param array $result The results to merge.
     * @return array
     */
    private function mergeWithMessage($result)
    {
        if (array_key_exists(Responses::KEY_ERROR_CODE, $result)) {
            $result[Responses::KEY_MESSAGE] = $this->translateErrorCode($result[Responses::KEY_ERROR_CODE], $result);
        }

        return $result;
    }

}
