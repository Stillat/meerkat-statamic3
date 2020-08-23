<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSetCollection;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class LocalCommentChangeSetStorageManager
 *
 * Manages the interactions between Meerkat Comment Revisions and a local file system.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalCommentChangeSetStorageManager implements CommentChangeSetStorageManagerContract
{

    const PATH_REVISIONS = '.revisions';
    const KEY_SPECIAL_CONTENT = '*content*';
    const STR_CHECK_TIMESTAMP = 'timestamp: ';

    /**
     * The Meerkat Core configuration.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The Paths instance.
     *
     * @var Paths
     */
    protected $paths = null;

    /**
     * The YAMLParserContract implementation instance.
     *
     * @var YAMLParserContract
     */
    protected $yamlParser = null;

    public function __construct(Configuration $config, YAMLParserContract $yamlParser)
    {
        $this->config = $config;
        $this->paths = new Paths($this->config);
        $this->yamlParser = $yamlParser;
    }

    /**
     * Attempts to locate the change sets for the provided comment identifier.
     *
     * @param string $commentId The comment identifier.
     * @return ChangeSetCollection|null
     */
    public function getChangeSetForCommentId($commentId)
    {
        if ($this->config->trackChanges === false) {
            return null;
        }

        return $this->getChangeSetForComment(Comment::find($commentId));
    }

    /**
     * Attempts to locate the change sets for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return ChangeSetCollection
     */
    public function getChangeSetForComment(CommentContract $comment)
    {
        if ($this->config->trackChanges === false) {
            return new ChangeSetCollection();
        }

        $storagePath = $this->getChangeSetStoragePath($comment);

        if (file_exists($storagePath) === false) {
            return new ChangeSetCollection();
        }

        $contents = $this->yamlParser->parseDocument(file_get_contents($storagePath));

        if ($contents === null || is_array($contents) === false) {
            return new ChangeSetCollection();
        }

        return ChangeSetCollection::fromArray($contents);
    }

    /**
     * Retrieves the file path for the comment's revisions.
     *
     * @param CommentContract $comment The comment to locate the path for.
     * @return string
     */
    private function getChangeSetStoragePath(CommentContract $comment)
    {
        $storageDirectory = dirname($comment->getVirtualPath());

        return $this->paths->combine([
            $storageDirectory,
            self::PATH_REVISIONS
        ]);
    }

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param string $commentId The comment identifier.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSetById($commentId, ChangeSet $changeSet)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        return $this->addChangeSet(Comment::find($commentId), $changeSet);
    }

    /**
     * Attempts to add a single change set to the comment.
     *
     * @param CommentContract $comment The comment.
     * @param ChangeSet $changeSet The change set to add.
     * @return bool
     */
    public function addChangeSet(CommentContract $comment, ChangeSet $changeSet)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        $existingChangeSets = $this->getChangeSetForComment($comment);
        $storagePath = $this->getChangeSetStoragePath($comment);

        $existingChangeSets->setCurrentRevision($changeSet->getTimestampUtc());
        $existingChangeSets->addChangeSet($changeSet);

        $dataToSave = $this->yamlParser->toYaml($existingChangeSets->toArray(), null);

        $saveResults = file_put_contents($storagePath, $dataToSave);

        if ($saveResults === false) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the revision identifiers for the provided identifier.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRevisionsById($commentId)
    {
        if ($this->config->trackChanges === false) {
            return [];
        }

        return $this->getRevisions(Comment::find($commentId));
    }

    /**
     * Retrieves the revision identifiers for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return string[]
     */
    public function getRevisions(CommentContract $comment)
    {
        if ($this->config->trackChanges === false) {
            return [];
        }

        return $this->getChangeSetForComment($comment)->getChangeSetRevisions();
    }

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExistsById($commentId, $revision)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        return $this->revisionExists(Comment::find($commentId), $revision);
    }

    /**
     * Tests if a revision exists for the provided comment.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function revisionExists(CommentContract $comment, $revision)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        return in_array($revision, $this->getRevisions($comment));
    }

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param string $commentId The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSetsById($commentId)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        return $this->removeHistoricalChangeSets(Comment::find($commentId));
    }

    /**
     * Attempts to remove all revisions older than the current revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @return bool
     */
    public function removeHistoricalChangeSets(CommentContract $comment)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        $currentCollection = $this->getChangeSetForComment($comment);
        $currentChangeSets = $currentCollection->getChangeSets();
        $changesToReset = [];
        $currentRevision = $currentCollection->getCurrentRevision();

        foreach ($currentChangeSets as $change) {
            if ($change->getTimestampUtc() >= $currentRevision) {
                $changesToReset[] = $change;
            }
        }

        $currentCollection->setChangeSets($changesToReset);

        $storagePath = $this->getChangeSetStoragePath($comment);
        $dataToSave = $this->yamlParser->toYaml($currentCollection->toArray(), null);

        $saveResults = file_put_contents($storagePath, $dataToSave);

        if ($saveResults === false) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param string $commentId The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevisionById($commentId, $revision)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        return $this->updateToRevision(Comment::find($commentId), $revision);
    }

    /**
     * Attempts to update the comment to the specified revision.
     *
     * @param CommentContract $comment The comment identifier.
     * @param string $revision The revision identifier.
     * @return bool
     */
    public function updateToRevision(CommentContract $comment, $revision)
    {
        if ($this->config->trackChanges === false) {
            return false;
        }

        $currentCollection = $this->getChangeSetForComment($comment);

        if ($currentCollection->hasRevision($revision) === false) {
            return false;
        }

        $changeSet = $currentCollection->getChangeSetForRevision($revision);

        if ($changeSet === null) {
            return false;
        }

        $currentCollection->setCurrentRevision((int)$revision);

        $content = '';
        $propertiesToRestore = $changeSet->getNewProperties();
        $attributes = [];

        if (array_key_exists(self::KEY_SPECIAL_CONTENT, $propertiesToRestore)) {
            $content = $propertiesToRestore[self::KEY_SPECIAL_CONTENT];
        }

        foreach ($changeSet->getOriginalKeyOrder() as $property) {
            if (array_key_exists($property, $propertiesToRestore) && $property !== self::KEY_SPECIAL_CONTENT) {
                $attributes[$property] = $propertiesToRestore[$property];
            }
        }

        $contentToSave = $this->yamlParser->toYaml($attributes, $content);
        $revisionDataToSave = $this->yamlParser->toYaml($currentCollection->toArray(), null);
        $collectionStoragePath = $this->getChangeSetStoragePath($comment);

        $saveResults = file_put_contents($collectionStoragePath, $revisionDataToSave);
        $commentContentSaved = file_put_contents($comment->getVirtualPath(), $contentToSave);

        if ($saveResults === false || $commentContentSaved === false) {
            return false;
        }

        return true;
    }

    /**
     * Gets the revision count for the provided comment identifier.
     *
     * @param string $commentId The comment's identifier.
     * @return int
     */
    public function getRevisionCountById($commentId)
    {
        if ($this->config->trackChanges === false) {
            return 0;
        }

        return $this->getRevisionCount(Comment::find($commentId));
    }

    /**
     * Gets the revision count for the provided comment.
     *
     * @param CommentContract $comment The comment.
     * @return int
     */
    public function getRevisionCount(CommentContract $comment)
    {
        if ($this->config->trackChanges === false) {
            return 0;
        }

        $storagePath = $this->getChangeSetStoragePath($comment);

        if (file_exists($storagePath) === false) {
            return 0;
        }

        $potentialCount = 0;
        $handle = fopen($storagePath, 'r');

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $trimLine = trim($line);

                if (Str::startsWith($trimLine, self::STR_CHECK_TIMESTAMP)) {
                    $potentialCount += 1;
                }
            }

            fclose($handle);
        }

        return $potentialCount;
    }

}
