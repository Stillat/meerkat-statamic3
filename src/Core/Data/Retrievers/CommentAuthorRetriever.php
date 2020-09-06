<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

use Stillat\Meerkat\Core\Authoring\TransientIdGenerator;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;

/**
 * Class CommentAuthorRetriever
 *
 * Provides utilities to retrieve author information from a comment dataset.
 *
 * @package Stillat\Meerkat\Core\Data\Retrievers
 * @since 2.0.0
 */
class CommentAuthorRetriever
{

    /**
     * Extracts author details from the list of comments.
     *
     * @param array $data The CommentContract arrays.
     * @return array
     */
    public static function getAuthorsFromCommentArray($data)
    {
        $authorsToReturn = [];

        foreach ($data as $commentArray) {
            if (array_key_exists(CommentContract::KEY_AUTHOR, $commentArray) === false) {
                if (array_key_exists(CommentContract::INTERNAL_PARENT_AUTHOR, $commentArray) === false ||
                    $commentArray[CommentContract::INTERNAL_PARENT_AUTHOR] === null) {
                    continue;
                }
            }

            // Process the parent author details, if available.
            if (array_key_exists(CommentContract::INTERNAL_PARENT_AUTHOR, $commentArray)
                && $commentArray[CommentContract::INTERNAL_PARENT_AUTHOR] !== null) {
                $parentAuthorDetails = $commentArray[CommentContract::INTERNAL_PARENT_AUTHOR];
                $parentAuthorDetails = CommentAuthorRetriever::getAuthorFromArray($parentAuthorDetails);
                $parentAuthorId = $parentAuthorDetails[AuthorContract::KEY_USER_ID];

                if (array_key_exists($parentAuthorId, $authorsToReturn) === false) {
                    $authorsToReturn[$parentAuthorId] = $parentAuthorDetails;
                }
            }

            // Process the primary author details.
            if (array_key_exists(CommentContract::KEY_AUTHOR, $commentArray)) {
                $primaryAuthorDetails = $commentArray[CommentContract::KEY_AUTHOR];
                $primaryAuthorDetails = CommentAuthorRetriever::getAuthorFromArray($primaryAuthorDetails);
                $primaryAuthorId = $primaryAuthorDetails[AuthorContract::KEY_USER_ID];

                if (array_key_exists($primaryAuthorId, $authorsToReturn) === false) {
                    $authorsToReturn[$primaryAuthorId] = $primaryAuthorDetails;
                }
            }
        }

        return array_values($authorsToReturn);
    }

    /**
     * Processes the author prototype and returns the result.
     *
     * @param array $author The author prototype.
     * @return array
     */
    protected static function getAuthorFromArray($author)
    {
        if (array_key_exists(AuthorContract::KEY_USER_ID, $author)) {
            if ($author[AuthorContract::KEY_USER_ID] === null) {
                $author[AuthorContract::KEY_USER_ID] = TransientIdGenerator::getId($author);
            }
        }

        return $author;
    }

    /**
     * Gets a collection of unique authors in the dataset.
     *
     * @param CommentContract[] $data The data to analyze.
     * @return AuthorContract[]
     */
    public static function getAuthors($data)
    {
        $authorsToReturn = [];
        $authorIdentifiers = [];

        foreach ($data as $comment) {
            $author = $comment->getAuthor();

            if ($author === null) {
                continue;
            }

            $authorIdentifier = null;

            if ($author->getIsTransient()) {
                $authorIdentifier = $author->getEmailAddress();
            } else {
                $authorIdentifier = $author->getId();
            }

            if (in_array($authorIdentifier, $authorIdentifiers) == false) {
                $authorsToReturn[] = $author;
                $authorIdentifiers[] = $authorIdentifier;
            }
        }

        return $authorsToReturn;
    }

}
