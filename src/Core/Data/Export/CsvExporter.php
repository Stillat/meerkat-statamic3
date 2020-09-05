<?php

namespace Stillat\Meerkat\Core\Data\Export;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\Export\DataExporterContract;

class CsvExporter implements DataExporterContract
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
}
