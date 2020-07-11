<?php

namespace Stillat\Meerkat\Core\Storage\Data;

use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Helpers\Str;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

/**
 * Provides utilities and mechanisms for finding thread data
 *
 * @since 2.0.0
 */
class ThreadCommentRetriever
{

    /**
     * The Configuration instance to provide access to the shared file share.
     *
     * @var Stillat\Meerkat\Core\Configuration
     */
    private $config = null;

    /**
     * A list of all resolved comments.
     *
     * @var CommentContract[]
     */
    private $comments = [];

    /**
     * The current root-level comment thread being worked in.
     *
     * @var Stillat\Meerkat\Core\Contracts\Threads\ThreadContract
     */
    private $thread = null;

    /**
     * The YAML document parser implementation.
     *
     * @var Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract
     */
    private $yamlParser = null;

    /**
     * The Markdown parser implementation.
     *
     * @var Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract
     */
    private $markdownParser = null;

    /**
     * The comment factory implementation instance.
     *
     * @var Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract
     */
    private $commentFactory = null;

    /**
     * The author retriever implementation instance.
     *
     * @var \Stillat\Meerkat\Core\Storage\Data\CommentAuthorRetriever
     */
    private $authorRetriever = null;

    /**
     * Indicates whether the hierarchy should return a flattened list or not.
     *
     * @var boolean
     */
    private $returnFlatList = false;

    /**
     * The total number of comments in the thread.
     *
     * @var integer
     */
    private $totalCommentCount = 0;

    /**
     * The total number of top-level comments in the thread.
     *
     * @var integer
     */
    private $totalTopLevelCommentCount = 0;

    /**
     * The stream storage manager instance.
     *
     * @var ThreadStorageManagerContract
     */
    private $streamStorageManager = null;

    /**
     * Creates a new instance of ThreadCommentRetriever.
     *
     * @param Configuration $config
     * @param YAMLParserContract $yamlParser
     * @param MarkdownParserContract $markdownParser
     * @param CommentFactoryContract $factory
     * @param CommentAuthorRetriever $authorRetriever
     * @param ThreadStorageManagerContract $streamStorageManager
     */
    public function __construct(
        Configuration $config,
        YAMLParserContract $yamlParser,
        MarkdownParserContract $markdownParser,
        CommentFactoryContract $factory,
        CommentAuthorRetriever $authorRetriever,
        ThreadStorageManagerContract $streamStorageManager
    ) {
        $this->config = $config;
        $this->yamlParser = $yamlParser;
        $this->markdownParser = $markdownParser;
        $this->commentFactory = $factory;
        $this->authorRetriever = $authorRetriever;
        $this->streamStorageManager = $streamStorageManager;
    }

    /**
     * Sets the desired thread to retrieve comments for.
     *
     * @param ThreadContract $thread The thread to find comments for.
     * @return void
     */
    public function setThread(ThreadContract $thread)
    {
        $this->comments = [];
        $this->thread = $thread;
    }

    /**
     * Resets the retriever to a new state.
     *
     * @return void
     */
    public function reset()
    {
        $this->comments = [];
        $this->thread = null;
    }

    /**
     * Returns a mapping of comment identifiers and their instance.
     *
     * @return CommentContract[]
     */
    public function getCommentMapping()
    {
        return $this->comments;
    }

    /**
     * Sets whether or not the hierarchy methods should remove
     * non -roots from the first level of the resulting array.
     *
     * @param  boolean $shouldReturnFlatList
     * @return void
     */
    public function setReturnFlatList($shouldReturnFlatList)
    {
        $this->returnFlatList = $shouldReturnFlatList;
    }

    /**
     * Returns the number of all comments in the thread.
     *
     * @return int
     */
    public function getAllCommentsCount()
    {
        return $this->totalCommentCount;
    }

    /**
     * Returns the number of top-level comments in the thread.
     *
     * @return int
     */
    public function getRootCommentsCount()
    {
        return $this->totalTopLevelCommentCount;
    }

    /**
     * Returns all the comments located in the physical storage path for the context's thread.
     *
     * @return CommentContract[]
     */
    public function getComments()
    {
        $this->comments = $this->streamStorageManager->getAllComments($this->thread);

        return $this->comments;
    }

    /**
     * Returns all comments with child content nested under their parent.
     *
     * @return void
     */
    public function buildHierarchy()
    {
        // If we have not build the "flat" list of comments yet, let's do that.
        if ($this->comments == null || is_array($this->comments) == false || count($this->comments) === 0) {
            $this->getComments();
        }

        // If the comment is a reply, let's give it a reference to it's root comment.
        $this->comments = array_map(function (CommentContract $comment) {
            if ($comment->isReply() && $comment->getParentId() !== null) {
                if (array_key_exists($comment->getParentId(), $this->comments)) {
                    $comment->setParentComment($this->comments[$comment->getParentId()]);
                }
            }

            return $comment;
        }, $this->comments);

        $this->totalCommentCount = count($this->comments);

        // Build the nested comment tree.
        $this->comments = array_map(function (CommentContract $comment) {
            if ($comment->getHasReplies()) {
                $internalPath = $comment->getDataAttribute(CommentContract::INTERNAL_PATH);

                if ($internalPath !== null && mb_strlen(trim($internalPath)) > 0) {
                    // Remove the comment.md suffix.
                    $parentPath = $internalPath;
                    $commentLevel = mb_substr_count($parentPath, CommentContract::KEY_REPLIES);
                    $internalPath = mb_substr($internalPath, 0, -10);

                    $replies = array_filter(
                        $this->comments,
                        function (CommentContract $comment) use ($internalPath, $parentPath, $commentLevel) {
                            $replyPath = $comment->getDataAttribute(CommentContract::INTERNAL_PATH);
                            $replyLevel = mb_substr_count($replyPath, CommentContract::KEY_REPLIES);

                            if (($replyPath !== $parentPath) &&
                                mb_strlen($replyPath) > mb_strlen($parentPath) &&
                                (($replyLevel - 1) === $commentLevel) &&
                                Str::startsWith($replyPath, $internalPath)
                            ) {
                                $comment->alwaysReportCommentHasReplies();

                                if (!$this->returnFlatList) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    );

                    $comment->setReplies($replies);
                }
            }

            return $comment;
        }, $this->comments);

        uasort($this->comments, function ($a, $b) {
            return strnatcmp($a->getId(), $b->getId());
        });

        $topLevelComments = array_filter($this->comments, function (CommentContract $comment) {
            return !$comment->isReply();
        });

        $this->totalTopLevelCommentCount = count($topLevelComments);

        if (!$this->returnFlatList) {
            $this->comments = $topLevelComments;
        }

        return $this->comments;
    }
}
