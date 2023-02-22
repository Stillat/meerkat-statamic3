<?php

namespace Stillat\Meerkat\Core\Data\Export;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\Export\DataExporterContract;
use Stillat\Meerkat\Core\Data\FieldMapper;

/**
 * Class JsonExporter
 *
 * Exports the comments as a JSON document.
 *
 * @since 2.0.0
 */
class JsonExporter implements DataExporterContract
{
    /**
     * The header names.
     *
     * @var array
     */
    private $headers = [];

    /**
     * The data fields to export.
     *
     * @var array
     */
    private $dataFields = [];

    /**
     * The FieldMapper instance.
     *
     * @var FieldMapper
     */
    private $fieldMapper = null;

    public function __construct(FieldMapper $mapper)
    {
        $this->fieldMapper = $mapper;
    }

    /**
     * Exports the provided comments.
     *
     * @param  CommentContract[]  $comments The comments to export.
     * @return mixed
     */
    public function export($comments)
    {
        $data = [];
        $targetFields = $this->fieldMapper->rewriteFields($this->dataFields);

        foreach ($comments as $comment) {
            $data[] = $this->fieldMapper->getData($comment, $targetFields, false);
        }

        return json_encode($data);
    }

    /**
     * Gets the content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/json';
    }

    /**
     * Sets the property names.
     *
     * @param  array  $names The property names.
     */
    public function setPropertyNames($names)
    {
        $this->headers = $names;
    }

    /**
     * Sets which data properties to export.
     *
     * @param  array  $properties The properties to export.
     */
    public function setProperties($properties)
    {
        $this->dataFields = $properties;
    }
}
