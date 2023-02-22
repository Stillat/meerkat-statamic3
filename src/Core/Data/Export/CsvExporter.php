<?php

namespace Stillat\Meerkat\Core\Data\Export;

use Stillat\Meerkat\Core\Contracts\Data\Export\CsvWriterContract;
use Stillat\Meerkat\Core\Contracts\Data\Export\DataExporterContract;
use Stillat\Meerkat\Core\Data\FieldMapper;

/**
 * Class CsvExporter
 *
 * Exports the comments as a CSV document.
 *
 * @since 2.0.0
 */
class CsvExporter implements DataExporterContract
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
     * The CsvWriterContract implementation instance.
     *
     * @var CsvWriterContract
     */
    private $csvWriter = null;

    /**
     * The FieldMapper instance.
     *
     * @var FieldMapper
     */
    private $fieldMapper = null;

    public function __construct(CsvWriterContract $writer, FieldMapper $mapper)
    {
        $this->csvWriter = $writer;
        $this->fieldMapper = $mapper;
    }

    /**
     * Exports the provided comments.
     *
     * @param  array  $comments The comments to export.
     * @return string
     */
    public function export($comments)
    {
        $this->csvWriter->writeHeaders($this->headers);
        $data = [];
        $targetFields = $this->fieldMapper->rewriteFields($this->dataFields);

        foreach ($comments as $comment) {
            $data[] = $this->fieldMapper->getData($comment, $targetFields);
        }

        $this->csvWriter->writeData($data);

        return $this->csvWriter->getContents();
    }

    /**
     * Gets the content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/plain';
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
