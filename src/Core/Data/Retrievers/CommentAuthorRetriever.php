<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

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
