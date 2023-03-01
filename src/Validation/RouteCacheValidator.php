<?php

namespace Stillat\Meerkat\Validation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Stillat\Meerkat\Http\Controllers\Api\CheckForSpamController;
use Stillat\Meerkat\Http\Controllers\Api\CommentsController;
use Stillat\Meerkat\Http\Controllers\Api\ExportController;
use Stillat\Meerkat\Http\Controllers\Api\NotSpamController;
use Stillat\Meerkat\Http\Controllers\Api\PublishCommentController;
use Stillat\Meerkat\Http\Controllers\Api\RemoveCommentController;
use Stillat\Meerkat\Http\Controllers\Api\ReplyCommentController;
use Stillat\Meerkat\Http\Controllers\Api\ReportingController;
use Stillat\Meerkat\Http\Controllers\Api\SpamController;
use Stillat\Meerkat\Http\Controllers\Api\TaskController;
use Stillat\Meerkat\Http\Controllers\Api\TelemetryController;
use Stillat\Meerkat\Http\Controllers\Api\UnpublishCommentController;
use Stillat\Meerkat\Http\Controllers\Api\UpdateCommentController;
use Stillat\Meerkat\Http\Controllers\ConfigureController;
use Stillat\Meerkat\Http\Controllers\SocializeController;
use Stillat\Meerkat\Http\Emitter\Emit;

/**
 * Class RouteCacheValidator
 *
 * Checks the current route configuration to determine if Meerkat
 * routes are missing due to Laravel's route caching mechanics.
 *
 * @since 2.2.3
 */
class RouteCacheValidator
{
    const KEY_NAME = 'name';

    const KEY_ACTION = 'action';

    const ROUTE_CACHE_CLEAR = 'statamic.cp.utilities.meerkat-validation.meerkat.routes.clear.cache';

    const CATEGORY_CONTROL_PANEL_CONFIGURATION = 'cp_configuration';

    const CATEGORY_SPAM_API = 'spam_api';

    const CATEGORY_TELEMETRY_API = 'telemetry_api';

    const CATEGORY_MODERATION_API = 'moderation_api';

    const CATEGORY_SITE_SUBMISSION_API = 'submission_api';

    /**
     * The Router instance.
     *
     * @var Router
     */
    protected $router = null;

    /**
     * A collection of all route's names and actions.
     *
     * @var Collection
     */
    protected $routes = [];

    /**
     * A list of all route names.
     *
     * @var array
     */
    protected $routeNames = [];

    /**
     * A list of all route actions.
     *
     * @var array
     */
    protected $routeActions = [];

    /**
     * Indicates if the internal route information has been loaded.
     *
     * @var bool
     */
    private $hasLoadedRoutes = false;

    /**
     * Indicates if the are problems with the current route configuration.
     *
     * @var bool
     */
    private $hasIssues = false;

    /**
     * Indicates if the validator has already analyzed the current environment for issues.
     *
     * @var bool
     */
    private $hasCheckedProblems = false;

    /**
     * Indicates if Meerkat's utilities controller is available in the router.
     *
     * @var bool
     */
    private $canClearRouteCache = false;

    /**
     * A list of all required emissions; if these are not found, the
     * emitter's routes are not being loaded by the current env.
     *
     * @var string[]
     */
    protected $requiredEmitters = [
    ];

    /**
     * A list of all required Meerkat controller actions.
     *
     * @var array
     */
    protected $requiredActions = [];

    /**
     * A mapping of required actions and their categories.
     *
     * @var string[]
     */
    protected $callbackCategories = [];

    /**
     * A list of all missing action categories.
     *
     * @var array
     */
    protected $missingActionCategories = [];

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->buildRequiredActionsTable();
    }

    /**
     * Loads the validator's route information.
     */
    public function loadRoutes()
    {
        if ($this->hasLoadedRoutes === true) {
            return;
        }

        $routeDetails = collect($this->router->getRoutes())->map(function ($route) {
            return $this->getRouteDetails($route);
        });

        $this->routeNames = $routeDetails->pluck(self::KEY_NAME)->values()->all();
        $this->routeActions = $routeDetails->pluck(self::KEY_ACTION)->values()->all();
        $this->routes = $routeDetails;
        $this->hasLoadedRoutes = true;

        foreach ($this->callbackCategories as $actionName => $categoryName) {
            if (! in_array($actionName, $this->routeActions)) {
                $this->missingActionCategories[] = $categoryName;
            }
        }

        $this->missingActionCategories = array_values(array_unique($this->missingActionCategories));

        $this->canClearRouteCache = $this->hasRoute(self::ROUTE_CACHE_CLEAR);
    }

    /**
     * Gets a list of all missing categories.
     *
     * @return string[]
     */
    public function getMissingCategories()
    {
        return $this->missingActionCategories;
    }

    /**
     * Constructs the list of all required Meerkat controller actions, and their categories.
     */
    private function buildRequiredActionsTable()
    {
        $this->addActions(SocializeController::class, ['postSocialize'], self::CATEGORY_SITE_SUBMISSION_API);
        $this->addActions(TelemetryController::class, [
            'index', 'getReport', 'submitReport',
        ], self::CATEGORY_TELEMETRY_API);
        $this->addActions(ConfigureController::class, [
            'getConfiguration',
            'save',
            'getCurrentConfigHash',
            'validateAkismetApiKey',
            'updateUserPerPage',
        ], self::CATEGORY_CONTROL_PANEL_CONFIGURATION);
        $this->addActions(CommentsController::class, ['search'], self::CATEGORY_MODERATION_API);
        $this->addActions(UpdateCommentController::class, ['updateComment'], self::CATEGORY_MODERATION_API);
        $this->addActions(ReplyCommentController::class, ['reply'], self::CATEGORY_MODERATION_API);
        $this->addActions(PublishCommentController::class, ['publishComment', 'publishMany'], self::CATEGORY_MODERATION_API);
        $this->addActions(UnpublishCommentController::class, ['unPublishComment', 'unPublishMany'], self::CATEGORY_MODERATION_API);
        $this->addActions(RemoveCommentController::class, ['deleteComment', 'deleteMany'], self::CATEGORY_MODERATION_API);
        $this->addActions(SpamController::class, ['markAsSpam', 'markManyAsSpam', 'removeAllSpam'], self::CATEGORY_MODERATION_API);
        $this->addActions(NotSpamController::class, ['markAsNotSpam', 'markManyAsNotSpam'], self::CATEGORY_MODERATION_API);
        $this->addActions(CheckForSpamController::class, ['checkForSpam'], self::CATEGORY_SPAM_API);
        $this->addActions(TaskController::class, ['getTaskStatus'], self::CATEGORY_SPAM_API);
        $this->addActions(ExportController::class, ['csv', 'json'], self::CATEGORY_MODERATION_API);
        $this->addActions(ReportingController::class, ['getReportOverview'], self::CATEGORY_MODERATION_API);
    }

    /**
     * Adds all of the provided class methods to the required actions list.
     *
     * @param  string  $class The class name.
     * @param  string[]  $methods The method names.
     * @param  string  $category The method category.
     */
    private function addActions($class, $methods, $category)
    {
        foreach ($methods as $method) {
            $callbackString = $this->toCallbackString($class, $method);

            $this->requiredActions[] = $callbackString;

            $this->callbackCategories[$callbackString] = $category;
        }
    }

    /**
     * Constructs a callback string.
     *
     * @param  string  $class The class name.
     * @param  string  $method The method name.
     * @return string
     */
    private function toCallbackString($class, $method)
    {
        return '\\'.$class.'@'.$method;
    }

    /**
     * Indicates if the required route to clear the route cache from the Control Panel is available.
     *
     * @return bool
     */
    public function canClearRouteCacheFromUi()
    {
        return $this->canClearRouteCache;
    }

    /**
     * Determines if the environment has the provided route.
     *
     * @param  string  $routeName The route's name.
     * @return bool
     */
    public function hasRoute($routeName)
    {
        return in_array($routeName, $this->routeNames);
    }

    /**
     * Indicates if any environment route issues were detected.
     *
     * @return bool
     */
    public function hasIssues()
    {
        if ($this->hasCheckedProblems === true) {
            return $this->hasIssues;
        }

        $missingEmissions = $this->getMissingEmissions();

        if (count($missingEmissions) > 0) {
            $this->hasIssues = true;
        }

        $this->hasCheckedProblems = true;

        return $this->hasIssues;
    }

    /**
     * Returns all missing asset emissions routes.
     *
     * @return string[]
     */
    public function getMissingEmissions()
    {
        return array_diff($this->requiredEmitters, Emit::$registeredEmitters);
    }

    /**
     * Returns the route's name and action as an array.
     *
     * @param  Route  $route The route.
     * @return array
     */
    private function getRouteDetails(Route $route)
    {
        return [
            self::KEY_NAME => $route->getName(),
            self::KEY_ACTION => $route->getActionName(),
        ];
    }

    /**
     * Attempts to automatically clear the Laravel route cache.
     */
    public static function attemptToCorrectRoutes()
    {
        /** @var RouteCacheValidator $validator */
        $validator = app(RouteCacheValidator::class);
        $validator->loadRoutes();

        if ($validator->hasIssues() && app()->routesAreCached()) {
            /** @var Filesystem $files */
            $files = app(Filesystem::class);
            $files->delete(app()->getCachedRoutesPath());
        }
    }
}
