<?php

namespace Stillat\Meerkat\Core\Authoring;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class TransientIdGenerator
 *
 * Provides utilities for converting identifying author properties into a GUID-like string.
 *
 * @package Stillat\Meerkat\Core\Authoring
 * @since 2.0.0
 */
class TransientIdGenerator
{

    /**
     * Converts the identifying information into a GUID-like string.
     *
     * @param array $authorPrototype The author prototype.
     * @return string|null
     */
    public static function getId($authorPrototype)
    {
        $idComponents = [];

        if (array_key_exists(CommentContract::KEY_EMAIL, $authorPrototype)) {
            $idComponents[] = mb_strtolower($authorPrototype[CommentContract::KEY_EMAIL]);
        }

        if (array_key_exists(CommentContract::KEY_NAME, $authorPrototype)) {
            $idComponents[] = mb_strtolower($authorPrototype[CommentContract::KEY_NAME]);
        }

        if (count($idComponents) === 0) {
            return null;
        }

        $hashedComponents = str_split(md5(join($idComponents)), 4);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', $hashedComponents);
    }

}
