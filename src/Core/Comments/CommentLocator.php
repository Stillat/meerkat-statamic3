<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Search\Engine;

/**
 * Provides utilities and mechanisms for locating Meerkat comments easily.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class CommentLocator
{

    /**
     * The Thread Manager instance.
     *
     * @var ThreadManagerContract
     */
    private $threadManager = null;

    private $commentSearchAttributes = [
        CommentContract::KEY_CONTENT, CommentContract::KEY_RAW_CONTENT
    ];

    /**
     * The search options to apply when locating comments and threads.
     *
     * @var SearchOptions
     */
    private $searchOptions = null;

    public function __construct(ThreadManagerContract $manager)
    {
        $this->threadManager = $manager;
        $this->searchOptions = new SearchOptions;
    }

    /**
     * Sets the search options.
     *
     * @param SearchOptions $searchOptions The search options to apply.
     */
    public function setSearchOptions($searchOptions)
    {
        $this->searchOptions = $searchOptions;
    }

    /**
     * Locates threads and comments, according to the provided search options.
     *
     * @return CommentCollection
     */
    public function locate()
    {
        $collectionToReturn = new CommentCollection;

        $shouldSearchWithinAuthors = $this->searchOptions->hasAuthorLimits();
        $authorsToCheck = [];

        $shouldPerformArbitraryTextSearch = $this->searchOptions->hasSearchTerms();
        $searchTerms = '';

        if ($shouldSearchWithinAuthors) {
            $authorsToCheck = $this->searchOptions->getAuthorLimiters();

            // Turn the author values into keys, this way we can use array_key_exists.
            $authorsToCheck = array_flip($authorsToCheck);
        }

        if ($shouldPerformArbitraryTextSearch) {
            $searchTerms = $this->searchOptions->searchTerms;
        }

        $threadsToSearch = [];
        $allComments = [];
        $threadsToMaterialize = [];
        $materializedThreads = [];

        // Has the user indicated that they would like to filter based on the author,
        // but has not specified any authors to actually check? If so, let's bail.
        if ($shouldSearchWithinAuthors === true && count($authorsToCheck) === 0) {
            return $collectionToReturn;
        }

        /* If the search options contain a subset of the threads to
         * search, we will use that user-provided subset instead.
         * If not, we will look all thread IDs currently stored.
         */
        if ($this->searchOptions->hasThreadLimits()) {
            $threadsToSearch = $this->searchOptions->getThreadLimiters();
        } else {
            $threadsToSearch = $this->threadManager->getAllThreadIds($this->searchOptions->includeTrashedThreads);
        }

        foreach ($threadsToSearch as $thread) {
            $threadComments = $this->threadManager->allForId($thread);
            $commentsMergedWithReturnValues = 0;

            // If the user has supplied arbitrary text search filters, let's
            // narrow down the list of potential comments before moving on.
            if ($shouldPerformArbitraryTextSearch) {
                $threadSearchEngine = new Engine;
                $threadSearchEngine->setSearchAttributes($this->commentSearchAttributes);

                $threadComments = $threadSearchEngine->search($searchTerms, $threadComments);
            }

            foreach ($threadComments as $comment) {
                // A few pre-checks before we get into the more granular filters.
                if ($shouldSearchWithinAuthors) {
                    $commentAuthorToCheck = $comment->getDataAttribute(AuthorContract::KEY_EMAIL_ADDRESS, null);

                    // For now, if there is no author information available, we will count that as a non-match.
                    if ($commentAuthorToCheck === null || mb_strlen(trim($commentAuthorToCheck)) === 0) {
                        continue;
                    }

                    if (!array_key_exists(trim($commentAuthorToCheck), $authorsToCheck)) {
                        continue;
                    }
                }

                if ($this->searchOptions->returnOnlyCommentsWithReplies === true && $this->searchOptions->returnOnlyParentComments === true) {
                    // Only return comment if it:
                    //    1. Has the `is_parent` attribute set to `true` AND
                    //    2. Has the `children` attribute set and at least one value.
                    if (count($comment->getDataAttribute(CommentContract::KEY_CHILDREN, [])) > 0 &&
                        $comment->getDataAttribute(CommentContract::KEY_IS_PARENT, false) === true) {
                        $allComments[] = $comment;
                        $commentsMergedWithReturnValues += 1;
                    }
                } else if ($this->searchOptions->returnOnlyParentComments) {
                    // Only return comment if it:
                    //    1. Has the `is_parent` attribute set to `true`
                    if ($comment->getDataAttribute(CommentContract::KEY_IS_PARENT, false) === true) {
                        $allComments[] = $comment;
                        $commentsMergedWithReturnValues += 1;
                    }
                } else if ($this->searchOptions->returnOnlyCommentsWithReplies === true) {
                    // Only return comment if it:
                    //    1. Has the `children` attribute set and at least one value.
                    if (count($comment->getDataAttribute(CommentContract::KEY_CHILDREN, [])) > 0) {
                        $allComments[] = $comment;
                        $commentsMergedWithReturnValues += 1;
                    }
                } else {
                    // Add all comments to return value if we hit this execution branch.
                    $allComments[] = $comment;
                    $commentsMergedWithReturnValues += 1;
                }
            }

            // If the thread contains comments, let's make sure get the full thread details later.
            if ($commentsMergedWithReturnValues > 0) {
                $threadsToMaterialize[] = $thread;
            }
        }

        foreach ($threadsToMaterialize as $threadId) {
            $materializedThreads[] = $this->threadManager->findById($threadId, $this->searchOptions->includeTrashedThreads, false);
        }

        $collectionToReturn->comments = $allComments;
        $collectionToReturn->threads = $materializedThreads;

        $collectionToReturn->threadCount = count($threadsToMaterialize);
        $collectionToReturn->commentCount = count($allComments);

        return $collectionToReturn;
    }

}