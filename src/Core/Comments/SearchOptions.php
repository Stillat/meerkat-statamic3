<?php

namespace Stillat\Meerkat\Core\Comments;

/**
 * Class SearchOptions
 *
 * Defines the various search options available when locating Meerkat comments.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class SearchOptions
{

    /**
     * Indicates if the locator include trashed threads.
     *
     * @var bool
     */
    public $includeTrashedThreads = false;

    /**
     * A comma-delimited list of threads to limit the search to.
     *
     * @var string The threads to limit to.
     */
    public $limitToThreads = '';

    /**
     * A comma-delimited list of author identifiers to limit the search to.
     *
     * @var string The comment authors to limit results to.
     */
    public $limitToAuthors = '';

    /**
     * Indicates whether comments without replies should be ignored.
     *
     * @var bool Whether or not to limit to only comments with replies.
     */
    public $returnOnlyCommentsWithReplies = false;

    /**
     * Indicates whether comments that are just replies to top-level comments are ignored.
     *
     * @var bool Whether or not to limit to only top-level comments.
     */
    public $returnOnlyParentComments = false;

    /**
     * Specifies an arbitrary search phrase comments must match against.
     *
     * @var string Arbitrary search phrases to match against.
     */
    public $searchTerms = '';

    /**
     * Indicates if arbitrary search terms were supplied by the user.
     *
     * @return bool
     */
    public function hasSearchTerms()
    {
        if ($this->searchTerms === null || mb_strlen(trim($this->searchTerms)) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns a collection of all authors to limit the search to.
     *
     * Note: This method will internally call haAuthorLimits().
     *
     * @return string[]
     */
    public function getAuthorLimiters()
    {
        if ($this->hasAuthorLimits() === false) {
            return [];
        }

        $authorsToReturn = explode(',', $this->limitToAuthors);

        if ($authorsToReturn === false) {
            return [];
        }

        foreach ($authorsToReturn as $key => $value) {
            $authorsToReturn[$key] = trim($value);
        }

        return $authorsToReturn;
    }

    /**
     * Indicates if the search options contains author limiters.
     *
     * @return bool
     */
    public function hasAuthorLimits()
    {
        if ($this->limitToAuthors === null || mb_strlen(trim($this->limitToAuthors)) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns a collection of all threads to limit the search to.
     *
     * Note: This method will internally call hasThreadLimits().
     *
     * @return string[]
     */
    public function getThreadLimiters()
    {
        if ($this->hasThreadLimits() === false) {
            return [];
        }

        $threadsToReturn = explode(',', $this->limitToThreads);

        if ($threadsToReturn === false) {
            return [];
        }

        foreach ($threadsToReturn as $key => $value) {
            $threadsToReturn[$key] = trim($value);
        }

        return $threadsToReturn;
    }

    /**
     * Indicates if the search options contains thread limiters.
     *
     * @return bool
     */
    public function hasThreadLimits()
    {
        if ($this->limitToThreads === null || mb_strlen(trim($this->limitToThreads)) === 0) {
            return false;
        }

        return true;
    }

}
