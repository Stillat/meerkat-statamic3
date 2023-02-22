<?php

namespace Stillat\Meerkat\Core\Contracts\Data\Export;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Interface DataExporterContract
 *
 * Represents a CommentContract data exporter.
 *
 * @since 2.0.0
 */
interface DataExporterContract
{
    /**
     * Sets the property names.
     *
     * @param  array  $names The property names.
     */
    public function setPropertyNames($names);

    /**
     * Sets which data properties to export.
     *
     * @param  array  $properties The properties to export.
     */
    public function setProperties($properties);

    /**
     * Exports the provided comments.
     *
     * @param  CommentContract[]  $comments The comments to export.
     * @return string
     */
    public function export($comments);

    /**
     * Gets the content type.
     *
     * @return string
     */
    public function getContentType();
}
