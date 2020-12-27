<?php

namespace Stillat\Meerkat\Tags;

use Illuminate\Contracts\Container\BindingResolutionException;
use Statamic\Modifiers\CoreModifiers;
use Stillat\Meerkat\Addon as MeerkatAddon;
use Stillat\Meerkat\Concerns\GetsHiddenContext;
use Stillat\Meerkat\Core\Authoring\InitialsGenerator;
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
use Stillat\Meerkat\Tags\Authors\InitialsColors;
use Stillat\Meerkat\Tags\Authors\InitialsTag;
use Stillat\Meerkat\Tags\Responses\CollectionRenderer;
use Stillat\Meerkat\Tags\Testing\OutputThreadDebugInformation;

/**
 * Class Meerkat
 *
 * The main Meerkat Antlers tags integration.
 *
 * @package Stillat\Meerkat\Tags
 * @since 2.0.0
 */
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
     * {{ meerkat:gravatar }}
     *
     * @return string
     */
    public function gravatar()
    {
        $gravatarValue = $this->gravatarValue();

        return '//www.gravatar.com/avatar/' . $gravatarValue . '?';
    }

    /**
     * {{ meerkat:gravatar_value }}
     *
     * @return string
     */
    public function gravatarValue()
    {
        $email = '';

        if ($this->context !== null) {
            $email = $this->context->get('email');
        }

        return md5($email);
    }

    /**
     * {{ meerkat:identicon }}
     *
     * @return string
     */
    public function identicon()
    {
        $value = $this->identiconValue();

        return 'https://avatars.dicebear.com/v2/identicon/' . $value . '.svg';
    }

    /**
     * {{ meerkat:identicon_value }}
     *
     * @return string
     */
    public function identiconValue()
    {
        return $this->gravatarValue();
    }

    /**
     * {{ meerkat:jdenticon }}
     */
    public function jdenticon()
    {
        $value = $this->jdenticonValue();

        return 'https://avatars.dicebear.com/v2/jdenticon/' . $value . '.svg';
    }

    /**
     * {{ meerkat:jdenticon_value }}
     *
     * @return string
     */
    public function jdenticonValue()
    {
        return $this->gravatarValue();
    }

    /**
     * {{ meerkat:simple_background_color }}
     *
     * @return string
     */
    public function simpleBackgroundColor()
    {
        return 'rgb(142,142,147)';
    }

    /**
     * {{ meerkat:simple_foreground_color }}
     *
     * @return string
     */
    public function simpleForegroundColor()
    {
        return '#ffffff';
    }

    /**
     * {{ meerkat:initials_background_color }}
     *
     * @return string
     */
    public function initialsBackgroundColor()
    {
        $value = $this->getParameterValue('value', null);

        if ($value === null) {
            if ($this->context !== null) {
                $value = $this->context->get('name');
                $value = InitialsGenerator::getInitials($value);
            }
        }

        if (mb_strlen(trim($value)) > 0) {
            $colors = InitialsColors::getColors($value[0]);

            if ($colors !== null && is_array($colors) && count($colors) === 2) {
                return $colors[0];
            }
        }

        return InitialsColors::$defaultBackgroundColor;
    }

    /**
     * {{ meerkat:initials_foreground_color }}
     *
     * @return string
     */
    public function initialsForegroundColor()
    {
        $value = $this->getParameterValue('value', null);

        if ($value === null) {
            if ($this->context !== null) {
                $value = $this->context->get('name');
                $value = InitialsGenerator::getInitials($value);
            }
        }

        if (mb_strlen(trim($value)) > 0) {
            $colors = InitialsColors::getColors($value[0]);

            if ($colors !== null && is_array($colors) && count($colors) === 2) {
                return $colors[1];
            }
        }

        return InitialsColors::$defaultForegroundColor;
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

    public function commentCount()
    {
        return $this->count();
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

        $count = 0;

        if ($thread !== null) {
            /** @var DataSetContract $queryResults */
            $queryResults = $thread->query(function (DataQuery $builder) {
                $this->applyParamFiltersToQuery($builder);
                return $builder;
            });

            $count = $queryResults->count();
        }

        if ($this->hasParameterValue('format_number')) {
            $numberFormat = explode('|', $this->getParameterValue('format_number', '1|.|,'));

            $modifiers = new CoreModifiers();

            return $modifiers->formatNumber($count, $numberFormat);
        }

        return $count;
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
     * Provides simpler access to the underlying initials system.
     *
     * {{ meerkat:initials }}
     *
     * @return string
     * @throws BindingResolutionException
     * @throws TemplateTagsException
     */
    public function initials()
    {
        return $this->renderDynamic(InitialsTag::class);
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
