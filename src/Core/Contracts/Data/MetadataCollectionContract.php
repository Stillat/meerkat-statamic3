<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;

/**
 * Interface MetadataCollectionContract
 *
 * Provides a consistent API for managing comment dataset meta data.
 *
 * @since 2.0.0
 */
interface MetadataCollectionContract
{
    /**
     * Gets a list of unique comment identifiers in the dataset.
     *
     * @return string[]
     */
    public function getCommentIds();

    /**
     * Gets a list of unique thread identifiers in the dataset.
     *
     * @return string[]
     */
    public function getThreadIds();

    /**
     * Gets a list of unique authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAuthors();

    /**
     * Gets a list of unique author email addresses in the dataset.
     *
     * @return string[]
     */
    public function getAuthorEmailAddresses();

    /**
     * Gets a list of unique authenticated authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAuthenticatedAuthors();

    /**
     * Gets a list of unique anonymous authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAnonymousAuthors();

    /**
     * Gets a list of unique authenticated author identifiers.
     *
     * @return string[]
     */
    public function getAuthenticatedAuthorIds();

    /**
     * Gets a list of unique authenticated author email addresses.
     *
     * @return string[]
     */
    public function getAuthenticatedAuthorEmailAddresses();

    /**
     * Gets a list of unique anonymous author email addresses.
     *
     * @return string[]
     */
    public function getAnonymousAuthorEmailAddresses();
}
