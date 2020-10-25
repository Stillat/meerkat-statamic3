<?php

namespace Stillat\Meerkat\Tags;

use Illuminate\Contracts\Container\BindingResolutionException;
use Stillat\Meerkat\Addon as MeerkatAddon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Exceptions\TemplateTagsException;
use Stillat\Meerkat\Forms\MeerkatForm;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Tags\Responses\CollectionRenderer;
use Stillat\Meerkat\Tags\Testing\OutputThreadDebugInformation;

// TODO: Apply query from params.
// TODO: Ensure that trashed comments are correctly filtered.
// TODO: Ensure that trashed threads are correctly ignored.
class Meerkat extends MeerkatTag
{
    use GetsHiddenContext;

    private $threadManager = null;

    /**
     * The SanitationManagerContract implementation instance.
     *
     * @var SanitationManagerContract
     */
    private $sanitizer = null;

    /**
     * The context resolver implementation instance.
     *
     * @var ContextResolverContract
     */
    private $contextResolver = null;


    /**
     * The Meerkat Core configuration container.
     *
     * @var Configuration
     */
    private $config;

    public function __construct(Configuration $config,
                                CommentFilterManager $filterManager,
                                ThreadManagerContract $threadManager,
                                SanitationManagerContract $sanitizer)
    {
        parent::__construct($filterManager);

        $this->config = $config;
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
     * @throws TemplateTagsException
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
     * @throws TemplateTagsException
     */
    public function form()
    {
        return $this->renderDynamic(MeerkatForm::class);
    }

    /**
     * @param $className
     * @param null $instanceCallback
     * @return string
     * @throws BindingResolutionException
     * @throws TemplateTagsException
     */
    private function renderDynamic($className, $instanceCallback = null)
    {
        if ($className !== null && mb_strlen(trim($className)) > 0) {
            /** @var MeerkatTag $instance */
            $instance = app()->make($className);
            $instance->setFromContext($this);

            if ($instanceCallback !== null && is_callable($instanceCallback)) {
                $instance = $instanceCallback($instance);

                if ($instance === null || ($instance instanceof MeerkatTag) === false) {
                    throw new TemplateTagsException('Instance callback must return instance of ' . $className);
                }
            }

            return $instance->render();
        }

        return '';
    }

    /**
     * Display theme Meerkat diagnostics data, for the current scope.
     *
     * Maps to {{ meerkat:debug }}
     *
     * @return string
     * @throws BindingResolutionException
     * @throws TemplateTagsException
     */
    public function debug()
    {
        return $this->renderDynamic(OutputThreadDebugInformation::class);
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
     * Returns the number of published, not-spam comments.
     *
     * @return int
     */
    public function count()
    {
        // TODO: Allow override of query. Needs a global "query builder builder".
        $contextId = $this->getHiddenContext();
        $this->setFromContext($this);
        $thread = $this->threadManager->findById($contextId);

        if ($thread === null) {
            return 0;
        }

        /** @var DataSetContract $queryResults */
        $queryResults = $thread->query(function (DataQuery $builder) {
            $this->applyParamFiltersToQuery($builder);
            return $builder;
        });

        return $queryResults->count();
    }

    /**
     * {{ meerkat:all-comments }}
     *
     * @return string
     */
    public function allComments()
    {
        return $this->renderDynamic(CollectionRenderer::class);
    }

    /**
     * {{ meerkat:responses }}
     *
     * @return string|string[]
     * @throws BindingResolutionException
     * @throws TemplateTagsException
     */
    public function responses()
    {
        $contextId = $this->getHiddenContext();

        if ($contextId === null || mb_strlen(trim($contextId)) === 0) {
            return '';
        }

        return $this->renderDynamic(
            CollectionRenderer::class, function (CollectionRenderer $render) use ($contextId) {
            $render->tagContext = 'meerkat:responses';
            $render->setThreadId($contextId);

            return $render;
        });
    }

    /**
     * {{ meerkat:thread }}
     *
     * @return string|string[]
     * @throws BindingResolutionException
     * @throws TemplateTagsException
     */
    public function thread()
    {
        return $this->responses();
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

    /**
     * Returns the current Meerkat version.
     *
     * {{ meerkat:version }}
     *
     * @return string
     */
    public function version()
    {
        return MeerkatAddon::VERSION;
    }

    /**
     * Helper tag to evaluate if a URL parameter exists.
     *
     * {{ meerkat:has-input param="queryParamName" }}
     *
     * @return bool
     */
    public function hasInput()
    {
        $paramName = $this->params->get('param', null);

        if ($paramName === null) {
            return false;
        }

        $inputValue = request()->input($paramName, null);
        $valuesToCheck = $this->params->get('in', null);

        if ($valuesToCheck !== null) {
            $valuesArray = TypeConversions::parseToArray($valuesToCheck);

            return in_array($inputValue, $valuesArray);
        }

        return true;
    }

    /**
     * Renders the tag content.
     *
     * @return string
     */
    public function render()
    {
        return '';
    }
}
