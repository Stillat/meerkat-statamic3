<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Support\Arr;

/**
 * Class FieldMapper
 *
 * Allows for accessing common data using a shorthand accessor.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class FieldMapper
{

    /**
     * A mapping of shorthand fields and their data target.
     *
     * @var string[]
     */
    private $mappedFields = [
        'comment.date' => 'comment_date_formatted',
        'comment.content' => 'content_raw',
        'comment.is_spam' => 'spam',
        'comment.published' => 'published'
    ];

    /**
     * Retrieves the requested fields from the comment data.
     *
     * @param array $comment The comment data.
     * @param array $fields The fields to retrieve.
     * @param bool $transform Whether to transform the data.
     * @return array
     */
    public function getData($comment, $fields, $transform = true)
    {
        $props = [];

        foreach ($fields as $field) {
            $value = Arr::getValue($field, $comment, null);

            if ($transform) {
                $props[] = ValueTransformer::transform($value);
            } else {
                $props[] = $value;
            }
        }

        return $props;
    }

    /**
     * Rewrites the provided fields to their appropriate target.
     *
     * @param array $fields The fields to rewrite.
     * @return array
     */
    public function rewriteFields($fields)
    {
        $newFields = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $this->mappedFields)) {
                $newFields[] = $this->mappedFields[$field];
            } else {
                $newFields[] = $field;
            }
        }

        return $newFields;
    }

}
