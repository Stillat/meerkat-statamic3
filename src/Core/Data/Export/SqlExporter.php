<?php

namespace Stillat\Meerkat\Core\Data\Export;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\Export\DataExporterContract;

class SqlExporter implements DataExporterContract
{

    /**
     * Exports the provided comments.
     *
     * @param CommentContract[] $comments The comments to export.
     * @return mixed
     */
    public function export($comments)
    {
        // TODO: Implement export() method.
    }

    /**
     * Gets the content type.
     *
     * @return string
     */
    public function getContentType()
    {
        // TODO: Implement getContentType() method.
    }
}
