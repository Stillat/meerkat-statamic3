<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\MetadataCollectionContract;
use Stillat\Meerkat\Core\Data\Concerns\GetsAssociatedDatasetData;

/**
 * Class DataSetMetadata
 *
 * Represents a paged comment dataset meta data.
 *
 * @since 2.0.0
 */
class DataSetMetadata implements MetadataCollectionContract
{
    use GetsAssociatedDatasetData;

    /**
     * Sets the comment data to analyze.
     *
     * @var CommentContract[]
     */
    protected $data = [];

    /**
     * Sets the data to analyze.
     *
     * @param  CommentContract[]  $data The data to analyze.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Analyzes the internal data and clears the internal data collection.
     */
    public function processAndUnset()
    {
        // Simply invoke the various metadata methods to build internal caches.
        $this->getCommentIds();
        $this->getThreadIds();
        $this->getAuthors();
        $this->getAuthorEmailAddresses();
        $this->getAuthenticatedAuthors();
        $this->getAnonymousAuthors();
        $this->getAuthenticatedAuthorIds();
        $this->getAuthenticatedAuthorEmailAddresses();
        $this->getAnonymousAuthorEmailAddresses();

        unset($this->data);
        $this->data = [];
    }

    /**
     * Returns a flattened dataset collection.
     *
     * @return CommentContract[]
     */
    public function flattenDataset()
    {
        return $this->data;
    }
}
