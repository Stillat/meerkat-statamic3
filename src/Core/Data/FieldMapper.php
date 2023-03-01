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
        'comment.published' => 'published',
        'comment.context.id' => 'context.id',
        'comment.context.title' => 'context.title',
    ];

    /**
     * Indicates if values should be transformed for text output, or data output.
     *
     * @var bool
     */
    private $transformForText = false;

    /**
     * The text to use for true values.
     *
     * @var string
     */
    private $trueText = 'true';

    /**
     * The text to use for false values.
     *
     * @var string
     */
    private $falseText = 'false';

    /**
     * Sets the text to use for transformation values.
     *
     * @param string $trueText The text to use for true values.
     * @param string $falseText The text to use for false values.
     */
    public function setTextTransformValues($trueText, $falseText)
    {
        $this->trueText = $trueText;
        $this->falseText = $falseText;
    }

    /**
     * Sets whether to use text transformations.
     *
     * @param bool $doTextTransform Whether to use text transformations.
     */
    public function setUseTextTransform($doTextTransform)
    {
        $this->transformForText = $doTextTransform;
    }

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
                if ($this->transformForText === false) {
                    $props[] = ValueTransformer::transform($value);
                } else {
                    $props[] = ValueTransformer::transformText($value, $this->trueText, $this->falseText);
                }
            } else {
                $props[] = $value;
            }
        }

        return $props;
    }

    public function getDataWithKeys($comment, $fields, $transform = true)
    {
        $props = [];

        foreach ($fields as $field) {
            $value = Arr::getValue($field, $comment, null);

            if ($transform) {
                if ($this->transformForText === false) {
                    $props[$field] = ValueTransformer::transform($value);
                } else {
                    $props[$field] = ValueTransformer::transformText($value, $this->trueText, $this->falseText);
                }
            } else {
                $props[$field] = $value;
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
