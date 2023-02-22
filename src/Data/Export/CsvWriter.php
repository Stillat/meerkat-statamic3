<?php

namespace Stillat\Meerkat\Data\Export;

use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use SplTempFileObject;
use Stillat\Meerkat\Core\Contracts\Data\Export\CsvWriterContract;

/**
 * Class CsvWriter
 *
 * Implements the CsvWriterContract to provide CSV writing features.
 *
 * @since 2.0.0
 */
class CsvWriter implements CsvWriterContract
{
    /**
     * The Writer instance.
     *
     * @var Writer
     */
    private $writer = null;

    public function __construct()
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
    }

    /**
     * Writes the headers to the CSV file.
     *
     * @param  array  $headers The headers.
     *
     * @throws CannotInsertRecord
     */
    public function writeHeaders($headers)
    {
        $this->writer->insertOne($headers);
    }

    /**
     * Inserts the provided as individual rows.
     *
     * @param  array  $data The data to write.
     */
    public function writeData($data)
    {
        $this->writer->insertAll($data);
    }

    /**
     * Gets the contents of the CSV file.
     *
     * @return string
     */
    public function getContents()
    {
        return (string) $this->writer;
    }
}
