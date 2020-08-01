<?php

namespace Stillat\Meerkat\Tags\Responses;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Tags\MeerkatTag;
use Stillat\Meerkat\Tags\Output\PaginatedThreadRenderer;
use Stillat\Meerkat\Tags\Output\RecursiveThreadRenderer;

class CollectionRenderer extends MeerkatTag
{
    protected $threadManager = null;
    protected $sanitizer = null;
    protected $paginated = false;
    protected $pageLimit = null;
    protected $pageOffset = 0;
    protected $pageBy = 'page';

    private $threadId = null;

    public function __construct(ThreadManagerContract $threadManager, SanitationManagerContract $sanitizer)
    {
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
    }

    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;
    }

    /**
     * Renders the tag content.
     *
     * @return string
     */
    public function render()
    {
        $this->parseParameters();

        $collectionName = $this->getParam('as', 'comments');
        $flatList = $this->getParam('flat', false);

        $thread = $this->threadManager->findById($this->threadId);

        $displayComments = [];
        $comments = $thread->getCommentCollection($collectionName);

        foreach ($comments as $commentId => $comment) {
            $comments[$commentId] = $this->sanitizer->sanitizeArrayValues($comment);
        }

        if ($flatList === true) {
            $displayComments = $comments;
        } else {
            foreach ($comments as $comment) {
                if ($comment[CommentContract::KEY_DEPTH] === 1) {
                    $displayComments[] = $comment;
                }
            }
        }

        $displayComments = $this->prepareItems($displayComments);

        if ($this->get('group_by_date')) {
            // TODO: Implement...
        } else {
            if ($this->paginated) {
                return $this->parseComments(
                    PaginatedThreadRenderer::preparePaginatedThread($collectionName,
                        collect($displayComments),
                        $this->pageBy,
                        $this->pageOffset,
                        $this->pageLimit)
                    ,
                    [], $collectionName);
            } else {
                return $this->parseComments([
                    $collectionName => $displayComments
                ], [], $collectionName);
            }
        }
    }

    private function parseParameters()
    {
        $this->paginated = $this->getParam('paginate', false);
        $this->pageOffset = $this->getParam('offset', 0);
        $this->pageBy = $this->getParam('pageby', 'page');
        $this->pageLimit = $this->getParam('limit', null);
    }

    /**
     *
     * @param array $items The items to prepare.
     * @return array
     */
    private function prepareItems($items)
    {
        // TODO: Implement filters, etc.
        return $items;
    }

    /**
     * Renders a recursive thread.
     *
     * @param array $data The comment data.
     * @param array $context The render context.
     * @param string $collectionName The name of the collection.
     * @return string|string[]
     */
    protected function parseComments($data = [], $context = [], $collectionName = 'comments')
    {
        $metaData = [];

        if (array_key_exists('total_results', $data) === false) {
            $metaData = [
                'total_results' => count($data[$collectionName])
            ];
        }

        if (count($metaData) > 0) {
            $data = array_merge($data, $metaData);
        }

        return RecursiveThreadRenderer::renderRecursiveThread($this->sanitizer, $this->content, $data, $context, $collectionName);
    }

}