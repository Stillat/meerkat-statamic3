<?php

namespace Stillat\Meerkat\Core\Contracts\Data\Export;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

interface DataExporterContract
{

    /**
     * Exports the provided comments.
     *
     * @param CommentContract[] $comments The comments to export.
     * @return mixed
     */
    public function export($comments);

}