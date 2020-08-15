<?php

namespace Stillat\Meerkat\Tags\Responses;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Exceptions\FilterException;
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
    protected $query = null;

    private $threadId = null;
    private $paginatedThreadRenderer = null;

    public function __construct(
        ThreadManagerContract $threadManager,
        SanitationManagerContract $sanitizer,
        PaginatedThreadRenderer $pageRenderer,
        DataQuery $query)
    {
        $this->query = $query;
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
        $this->paginatedThreadRenderer = $pageRenderer;
    }

    /**
     * Sets the internal thread identifier.
     *
     * @param string $threadId The thread identifier.
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;
    }

    /**
     * Renders the tag content.
     *
     * @return string
     * @throws FilterException
     */
    public function render()
    {
        $this->parseParameters();

        $collectionName = $this->getParam('as', 'comments');
        $flatList = $this->getParam('flat', false);

        $thread = $this->threadManager->findById($this->threadId);

        $displayComments = [];
        $comments = $thread->getComments($collectionName);

        // TODO: Add user-defined sorting.
        $this->query->sortDesc(CommentContract::KEY_ID);

        $this->query->limit($this->pageLimit)->skip($this->pageOffset);

        if ($this->paginated) {
            $currentPage = request()->input($this->pageBy, 0);

            $this->query->pageBy($this->pageBy)->forPage($currentPage);
        }

        if ($this->get('group_by_date')) {
            $dateFormat = $this->get('group_by_date');

            $this->query->nameAllGroups('date_groups')->groupName('date_group')
                ->collectionName($collectionName)->groupBy('group:date', function (CommentContract $comment) use ($dateFormat) {
                    $comment->setDataAttribute('group:date', $comment->getCommentDate()->format($dateFormat));
                });
        }

        $results = $this->query->getCollection($comments, $collectionName);

        // TODO: Re-implement the views.

        dd($results, $comments);
        foreach ($results as $result) {
            dd('111', $result);
        }

        dd('adsf', $results);

        if ($flatList === true) {
            $displayComments = $comments;
        } else {
            foreach ($comments as $comment) {
                if ($comment[CommentContract::KEY_DEPTH] === 1) {
                    $displayComments[] = $comment;
                }
            }
        }

        if ($this->paginated) {
            return $this->parseComments(
                $this->paginatedThreadRenderer->preparePaginatedThread($collectionName,
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

    /**
     * Parses the tag's current context and sets the internal state.
     */
    private function parseParameters()
    {
        $this->paginated = $this->getParam('paginate', false);
        $this->pageOffset = $this->getParam('offset', 0);
        $this->pageBy = $this->getParam('pageby', 'page');
        $this->pageLimit = $this->getParam('limit', null);
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
