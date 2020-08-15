<?php

namespace Stillat\Meerkat\Tags;

use Illuminate\Contracts\Container\BindingResolutionException;
use Statamic\Tags\Tags;
use Stillat\Meerkat\Addon as MeerkatAddon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Forms\MeerkatForm;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Tags\Responses\CollectionRenderer;

class Meerkat extends Tags
{
    use GetsHiddenContext;

    private $threadManager = null;

    private $sanitizer = null;

    /**
     * The context resolver implementation instance.
     *
     * @var ContextResolverContract
     */
    private $contextResolver = null;

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
     * @throws BindingResolutionException
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
     * @throws BindingResolutionException
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
        return $this->threadManager->areCommentsEnabledForContext($this->getHiddenContext());
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
     * @return string|string[]
     * @throws BindingResolutionException
     */
    public function responses()
    {
        $contextId = $this->getHiddenContext();

        if ($contextId === null) {
            return '';
        }

        /** @var CollectionRenderer $collectionRenderer */
        $collectionRenderer = app()->make(CollectionRenderer::class);
        $collectionRenderer->setFromContext($this);
        $collectionRenderer->setThreadId($contextId);

        return $collectionRenderer->render();
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

        return '<script src="' . $scriptPath . '"></script>';
    }

    public function version()
    {
        return MeerkatAddon::VERSION;
    }

}
