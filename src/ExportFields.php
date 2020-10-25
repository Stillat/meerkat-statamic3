<?php

namespace Stillat\Meerkat;

/**
 * Class ExportFields
 *
 * Provides a central location to manage common export fields.
 *
 * @package Stillat\Meerkat
 * @since 2.1.5
 */
class ExportFields
{

    /**
     * Returns the standard export fields.
     *
     * @return string[]
     */
    public static function getExportFields()
    {
        return [
            'comment.date',
            'author.name',
            'author.email',
            'author.user_agent',
            'author.user_ip',
            'author.referer',
            'comment.content',
            'comment.is_spam',
            'comment.published'
        ];
    }

}
