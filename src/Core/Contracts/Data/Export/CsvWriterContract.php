<?php

namespace Stillat\Meerkat\Core\Contracts\Data\Export;

/**
 * Interface CsvWriterContract
 *
 * Provides a consistent API for writing simple CSV files.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data\Export
 * @since 2.0.0
 */
interface CsvWriterContract
{

    /**
     * Writes the headers to the CSV file.
     *
     * @param array $headers The headers.
     */
    public function writeHeaders($headers);

    /**
     * Inserts the provided as individual rows.
     *
     * @param array $data The data to write.
     */
    public function writeData($data);

    /**
     * Gets the contents of the CSV file.
     *
     * @return string
     */
    public function getContents();

}
