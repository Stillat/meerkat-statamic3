<?php

namespace Stillat\Meerkat\Tags;

use Statamic\Tags\Tags;
use Stillat\Meerkat\Addon as MeerkatAddon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Forms\MeerkatForm;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Tags\Output\RecursiveThreadRenderer;

class Meerkat extends Tags
{
    use GetsHiddenContext;

    private $threadManager = null;

    private $sanitizer = null;

    public function __construct(ThreadManagerContract $threadManager, SanitationManagerContract $sanitizer)
    {
        $this->threadManager = $threadManager;
        $this->sanitizer = $sanitizer;
    }

    /**
     * {{ meerkat }}
     *
     * @return string
     */
    public function index()
    {
        return '';
    }

    /**
     * Renders a Meerkat form.
     *
     * Maps to {{ meerkat:create }}
     * Alias of {{ meerkat:form }}
     *
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function create()
    {
        return $this->form();
    }

    /**
     * Renders a Meerkat form.
     *
     * Maps to {{ meerkat:form }}
     *
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function form()
    {
        /** @var MeerkatForm $meerkatForm */
        $meerkatForm = app()->make(MeerkatForm::class);
        $meerkatForm->setFromContext($this);

        return $meerkatForm->render();
    }

    /**
     * Returns a value indicating if comments are enabled for the current page context.
     *
     * {{ meerkat:comments-enabled }}
     *
     * @return bool
     */
    public function commentsEnabled()
    {
        // TODO: Implement.
        return false;
    }

    /**
     * {{ meerkat:all-comments }}
     *
     * @return string
     */
    public function allComments()
    {
        // TODO: Implement.
        return '';
    }

    /**
     * {{ meerkat:responses }}
     *
     * @return string
     */
    public function responses()
    {
        $contextId = $this->getHiddenContext();

        if ($contextId === null) {
            return '';
        }

        $collectionName = $this->getParam('as', 'comments');
        $flatList = $this->getParam('flat', false);

        $thread = $this->threadManager->findById($contextId);

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

        return $this->parseComments([
            $collectionName => $displayComments
        ], [], $collectionName);
    }

    protected function parseComments($data = [], $context = [], $collectionName = 'comments')
    {
        $metaData = ['total_results' => count($data)];
        $data = array_merge($data, $metaData);

        return RecursiveThreadRenderer::renderRecursiveThread($this->sanitizer, $this->content, $data, $context, $collectionName);
    }

    /**
     * Creates an anchor link for the current comment context.
     *
     * {{ meerkat:cp-link }}
     *
     * @return string
     */
    public function cpLink()
    {
        $commentId = $this->getCurrentContextId();

        return '<a id="comment-"' . $commentId . '"></a>';
    }

    /**
     * Returns a Script element referencing Meerkat's reply JavaScript file.
     *
     * {{ meerkat:replies-to }}
     * @return string
     */
    public function repliesTo()
    {
        $scriptPath = PathProvider::publicJsVendorPath('replies-to');

        return '<script src="' . $scriptPath . '" />';
    }

    public function version()
    {
        return MeerkatAddon::VERSION;
    }

}