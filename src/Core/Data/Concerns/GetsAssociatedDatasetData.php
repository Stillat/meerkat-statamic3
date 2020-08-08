<?php

namespace Stillat\Meerkat\Core\Data\Concerns;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Data\Retrievers\CommentAuthorRetriever;
use Stillat\Meerkat\Core\Data\Retrievers\CommentIdRetriever;
use Stillat\Meerkat\Core\Data\Retrievers\CommentThreadIdRetriever;

/**
 * Trait GetsCommentIds
 *
 * Provides helpers to retrieve associated dataset data.
 *
 * @package Stillat\Meerkat\Core\Data\Concerns
 * @since 2.0.0
 *
 * @method CommentContract[] flattenDataset
 */
trait GetsAssociatedDatasetData
{

    /**
     * A cached list of comment identifiers.
     *
     * @var string[]
     */
    protected $cachedCommentIds = null;

    /**
     * A cached list of thread identifiers.
     *
     * @var string[]
     */
    protected $cachedThreadIds = null;

    /**
     * A cached list of comment authors.
     *
     * @var AuthorContract[]
     */
    protected $cachedAuthors = null;

    /**
     * A cached list of unique author email addresses.
     *
     * @var string[]
     */
    protected $cachedAuthorEmailAddresses = null;

    /**
     * A cached list of unique authenticated authors.
     *
     * @var AuthorContract[]
     */
    protected $cachedAuthenticatedAuthors = null;

    /**
     * A cached list of anonymous authors.
     *
     * @var AuthorContract[]
     */
    protected $cachedAnonymousAuthors = null;

    /**
     * A cached list of authenticated author identifiers.
     *
     * @var string[]
     */
    protected $cachedAuthenticatedAuthorIds = null;

    /**
     * A cached list of authenticated author email addresses.
     *
     * @var string[]
     */
    protected $cachedAuthenticatedAuthorEmailAddresses = null;

    /**
     * A cached list of anonymous author email addresses.
     *
     * @var string[]
     */
    protected $cachedAnonymousEmailAddresses = null;

    /**
     * Gets a list of unique thread identifiers in the dataset.
     *
     * @return string[]
     */
    public function getThreadIds()
    {
        if ($this->cachedThreadIds === null) {
            $this->cachedThreadIds = CommentThreadIdRetriever::getThreadIds($this->flattenDataset());
        }

        return $this->cachedThreadIds;
    }

    /**
     * Gets the comment identifiers from the data set.
     *
     * @return string[]
     */
    public function getCommentIds()
    {
        if ($this->cachedCommentIds === null) {
            $this->cachedCommentIds = CommentIdRetriever::getCommentIds($this->flattenDataset());
        }

        return $this->cachedCommentIds;
    }

    /**
     * Gets a list of unique author email addresses in the dataset.
     *
     * @return string[]
     */
    public function getAuthorEmailAddresses()
    {
        if ($this->cachedAuthorEmailAddresses === null) {
            $this->cachedAuthorEmailAddresses = [];

            foreach ($this->getAuthors() as $author) {
                if (!in_array($author->getEmailAddress(), $this->cachedAuthorEmailAddresses)) {
                    $this->cachedAuthorEmailAddresses[] = $author->getEmailAddress();
                }
            }
        }

        return $this->cachedAuthorEmailAddresses;
    }

    /**
     * Gets a list of unique authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAuthors()
    {
        if ($this->cachedAuthors === null) {
            $this->cachedAuthors = CommentAuthorRetriever::getAuthors($this->flattenDataset());
        }

        return $this->cachedAuthors;
    }

    /**
     * Gets a list of unique authenticated author identifiers.
     *
     * @return string[]
     */
    public function getAuthenticatedAuthorIds()
    {
        if ($this->cachedAuthenticatedAuthorIds === null) {
            $this->cachedAuthenticatedAuthorIds = [];

            foreach ($this->getAuthenticatedAuthors() as $author) {
                $this->cachedAuthenticatedAuthorIds[] = $author->getId();
            }
        }

        return $this->cachedAuthenticatedAuthorIds;
    }

    /**
     * Gets a list of unique authenticated authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAuthenticatedAuthors()
    {
        if ($this->cachedAuthenticatedAuthors === null) {
            $this->cachedAuthenticatedAuthors = [];

            foreach ($this->getAuthors() as $author) {
                if ($author->getIsTransient() === false) {
                    $this->cachedAuthenticatedAuthors[] = $author;
                }
            }
        }

        return $this->cachedAuthenticatedAuthors;
    }

    /**
     * Gets a list of unique authenticated author email addresses.
     *
     * @return string[]
     */
    public function getAuthenticatedAuthorEmailAddresses()
    {
        if ($this->cachedAuthenticatedAuthorEmailAddresses === null) {
            $this->cachedAuthenticatedAuthorEmailAddresses = [];

            foreach ($this->getAuthenticatedAuthors() as $author) {
                $this->cachedAuthenticatedAuthorEmailAddresses[] = $author->getEmailAddress();
            }
        }

        return $this->cachedAuthenticatedAuthorEmailAddresses;
    }

    /**
     * Gets a list of unique anonymous author email addresses.
     *
     * @return string[]
     */
    public function getAnonymousAuthorEmailAddresses()
    {
        if ($this->cachedAnonymousEmailAddresses === null) {
            $this->cachedAnonymousEmailAddresses = [];

            foreach ($this->getAnonymousAuthors() as $author) {
                $this->cachedAnonymousEmailAddresses[] = $author->getEmailAddress();
            }
        }

        return $this->cachedAnonymousEmailAddresses;
    }

    /**
     * Gets a list of unique anonymous authors in the dataset.
     *
     * @return AuthorContract[]
     */
    public function getAnonymousAuthors()
    {
        if ($this->cachedAnonymousAuthors === null) {
            $this->cachedAnonymousAuthors = [];

            foreach ($this->getAuthors() as $author) {
                if ($author->getIsTransient()) {
                    $this->cachedAnonymousAuthors[] = $author;
                }
            }
        }

        return $this->cachedAnonymousAuthors;
    }

}
