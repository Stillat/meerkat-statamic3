<?php

namespace Stillat\Meerkat;

use Statamic\Facades\Utility;
use Statamic\Statamic;
use Statamic\Yaml\ParseException;
use Stillat\Meerkat\Blueprint\BlueprintProvider;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Configuration\Manager;
use Stillat\Meerkat\Console\Commands\MigrateCommentsCommand;
use Stillat\Meerkat\Console\Commands\StatisticsCommand;
use Stillat\Meerkat\Console\Commands\ValidateCommand;
use Stillat\Meerkat\Core\Configuration as GlobalConfiguration;
use Stillat\Meerkat\Core\ConfigurationFactories;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Http\HttpClientContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterManagerContract;
use Stillat\Meerkat\Core\Contracts\Logging\ExceptionLoggerContract;
use Stillat\Meerkat\Core\Contracts\Mail\MailerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\DataPrivacyConfiguration;
use Stillat\Meerkat\Core\FormattingConfiguration;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Handlers\EmailHandler;
use Stillat\Meerkat\Core\Handlers\HandlerManager;
use Stillat\Meerkat\Core\Handlers\SpamServiceHandler;
use Stillat\Meerkat\Core\Http\Client;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Logging\GlobalLogState;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\Logging\MemoryErrorReporterManager;
use Stillat\Meerkat\Core\Logging\Reporters\SpatieRayReporter;
use Stillat\Meerkat\Core\Parsing\DateParserFactory;
use Stillat\Meerkat\Core\Parsing\MarkdownParserFactory;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Http\Composers\InstallValidationComposer;
use Stillat\Meerkat\Http\Controllers\UtilitiesController;
use Stillat\Meerkat\Logging\ExceptionLogger;
use Stillat\Meerkat\Mail\MeerkatMailer;
use Stillat\Meerkat\Parsing\CarbonDateParser;
use Stillat\Meerkat\Parsing\MarkdownParser;
use Stillat\Meerkat\Parsing\YAMLParser;
use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;
use Stillat\Meerkat\Providers\DataServiceProvider;
use Stillat\Meerkat\Providers\IdentityServiceProvider;
use Stillat\Meerkat\Providers\SpamServiceProvider;
use Stillat\Meerkat\Providers\TagsServiceProvider;
use Stillat\Meerkat\Providers\ThreadServiceProvider;
use Stillat\Meerkat\Support\Facades\Meerkat;

if (! defined('MEERKAT_COMMENTS')) {
    define('MEERKAT_COMMENTS', 210126);
}

/**
 * Class ServiceProvider
 *
 * Bootstraps the core Meerkat services, configuration, and utilities.
 *
 * @since 2.0.0
 */
class ServiceProvider extends AddonServiceProvider
{
    use UsesConfig, UsesTranslations;

    protected $defer = false;

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $composers = [
        'meerkat::validation' => InstallValidationComposer::class,
    ];

    protected $commands = [
        ValidateCommand::class,
        MigrateCommentsCommand::class,
        StatisticsCommand::class,
    ];

    protected $providers = [
        /** Start: Meerkat Core Dependency Providers */
        IdentityServiceProvider::class,
        SpamServiceProvider::class,
        ThreadServiceProvider::class,
        DataServiceProvider::class,
        /** End: Meerkat Core Dependency Providers */
        TagsServiceProvider::class,
        ControlPanelServiceProvider::class,
    ];

    protected $policies = [
    ];

    public function register()
    {
        GlobalLogState::$isApplicationInDebugMode = config('app.debug', false);

        $this->app->singleton(ErrorReporterManagerContract::class, function ($app) {
            return new MemoryErrorReporterManager();
        });
        ErrorReporterFactory::$instance = app(ErrorReporterManagerContract::class);

        $errorReporters = [];

        if ($this->getConfig('debug.auto_discover_spatie_ray', true) === true) {
            if (function_exists('\ray') && class_exists('\Spatie\Ray\Ray')) {
                $errorReporters[] = SpatieRayReporter::class;
            }
        }

        $configReporters = $this->getConfig('debug.reporters', []);

        if (is_array($configReporters)) {
            $errorReporters = array_merge($errorReporters, $configReporters);
        }

        if (count($errorReporters)) {
            foreach ($errorReporters as $reporter) {
                $reporterInstance = app($reporter);

                if ($reporterInstance !== null && $reporterInstance instanceof ErrorReporterContract) {
                    ErrorReporterFactory::$instance->registerReporter($reporterInstance);
                }
            }
        }
        $this->createPaths();

        $this->app->singleton(Manager::class, function ($app) {
            return new Manager();
        });

        Manager::$instance = app(Manager::class);

        Manager::$instance->loadConfiguration();

        // Registers the error log repository utilized by many Meerkat services and features.
        $this->registerMeerkatCoreErrorLogRepository();
        // Register Meerkat Core configuration containers.
        $this->registerMeerkatSpamGuardConfiguration();
        $this->registerMeerkatDataPrivacyConfiguration(); // Global Configuration relies on this.
        $this->registerMeerkatFormattingConfiguration(); // Global Configuration relies on the formatting config.
        $this->registerMeerkatGlobalConfiguration();
        $this->registerCoreDependencies();
        $this->checkIntegrationResourcesExist();
        $this->registerSubmissionHandler();

        parent::register();
    }

    /**
     * Creates the required storage paths for Meerkat.
     */
    private function createPaths()
    {
        $paths = [
            storage_path('meerkat/tmp'),
            storage_path('meerkat/tasks'),
            storage_path('meerkat/logs'),
            storage_path('meerkat/index'),
            base_path('meerkat'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path) === false) {
                mkdir($path, Paths::$directoryPermissions, true);
            }
        }

        // Create the helper files, if they don't exist.
        $helperFiles = [];
        $helperFiles[PathProvider::getStub('filters.php')] = 'filters.php';
        $helperFiles[PathProvider::getStub('events.php')] = 'events.php';

        foreach ($helperFiles as $source => $fileName) {
            $targetPath = base_path('meerkat/'.$fileName);

            if (! file_exists($targetPath)) {
                copy($source, $targetPath);
            }
        }
    }

    private function registerMeerkatCoreErrorLogRepository()
    {
        $targetPath = storage_path('meerkat/logs');

        if (file_exists($targetPath) == false) {
            mkdir($targetPath, 0755, true);
        }

        $this->app->singleton(ErrorCodeRepositoryContract::class, function ($app) use ($targetPath) {
            return new LocalErrorCodeRepository($targetPath);
        });

        // If we set the shared instance value, Meerkat Core can use it to report issues directly.
        LocalErrorCodeRepository::$instance = $this->app->make(ErrorCodeRepositoryContract::class);
    }

    /**
     * Creates the Meerkat Core data privacy configuration object.
     *
     * @since 2.1.14
     */
    private function registerMeerkatDataPrivacyConfiguration()
    {
        $this->app->singleton(DataPrivacyConfiguration::class, function ($app) {
            $privacyConfiguration = new DataPrivacyConfiguration();

            $privacyConfiguration->collectReferrer = $this->getConfig('privacy.store_referrer', true);
            $privacyConfiguration->collectUserAgent = $this->getConfig('privacy.store_user_agent', true);
            $privacyConfiguration->collectUserIp = $this->getConfig('privacy.store_user_ip', true);
            $privacyConfiguration->emptyName = $this->getConfig('privacy.anonymous_author', $this->trans('display.anonymous_author'));
            $privacyConfiguration->emptyEmailAddress = $this->getConfig('privacy.anonymous_email', $this->trans('display.anonymous_email'));

            return $privacyConfiguration;
        });
    }

    /**
     * Creates the Meerkat Core spam service and guard configuration object.
     */
    private function registerMeerkatSpamGuardConfiguration()
    {
        // Registers the configuration for the spam Guard service and providers.
        $this->app->singleton(GuardConfiguration::class, function ($app) {
            $guardConfiguration = new GuardConfiguration();

            $guardConfiguration->autoDeleteSpam = $this->getConfig('publishing.auto_delete_spam', false);
            $guardConfiguration->autoSubmitSpamToThirdParties = $this->getConfig('publishing.auto_submit_results', false);
            $guardConfiguration->checkAgainstAllGuardServices = $this->getConfig('publishing.guard_check_all_providers', false);
            $guardConfiguration->unpublishOnGuardFailures = $this->getConfig('publishing.guard_unpublish_on_guard_failure', false);
            $guardConfiguration->bannedWords = array_map('mb_strtolower', $this->getConfig('wordlist.banned', []));

            // Set the Akismet configuration data, if available.
            foreach ($this->getConfig('akismet', []) as $configSetting => $configValue) {
                $guardConfiguration->set('akismet_'.$configSetting, $configValue);
            }

            return $guardConfiguration;
        });
    }

    /**
     * Creates the Meerkat Core formatting configuration object.
     */
    private function registerMeerkatFormattingConfiguration()
    {
        $this->app->singleton(FormattingConfiguration::class, function ($app) {
            $formattingConfig = new FormattingConfiguration();

            $formattingConfig->tagsToKeep = $this->getConfig('formatting.keep_tags', '<a><p><ul><li><ol><code><pre>');
            $formattingConfig->commentDateFormat = $this->getConfig('formatting.comment_date_format', 'Y-m-d h:i:s A');

            // Register an additional configuration data, if available.
            foreach ($this->getConfig('formatting', []) as $configSetting => $configValue) {
                if ($configSetting !== 'remove_tags' && $configSetting !== 'comment_date_format') {
                    $formattingConfig->set($configSetting, $configValue);
                }
            }

            return $formattingConfig;
        });
    }

    /**
     * Creates the main Meerkat Core configuration object.
     */
    private function registerMeerkatGlobalConfiguration()
    {
        $this->app->singleton(GlobalConfiguration::class, function ($app) {
            $globalConfiguration = new GlobalConfiguration();

            $globalConfiguration->setFormattingConfiguration($app->make(FormattingConfiguration::class));
            $globalConfiguration->setDataPrivacyConfiguration($app->make(DataPrivacyConfiguration::class));

            // Permissions.
            $globalConfiguration->directoryPermissions = $this->getConfig('storage.permissions.directory', 777);
            $globalConfiguration->filePermissions = $this->getConfig('storage.permissions.file', 644);
            Paths::$directoryPermissions = $globalConfiguration->directoryPermissions;

            // General publishing settings.
            $globalConfiguration->autoPublishAnonymousPosts = $this->getConfig('publishing.auto_publish', false);
            $globalConfiguration->autoPublishAuthenticatedPosts = $this->getConfig('publishing.auto_publish_authenticated_users', false);
            $globalConfiguration->disableCommentsAfterDays = $this->getConfig('publishing.automatically_close_comments', 0);
            $globalConfiguration->trackChanges = $this->getConfig('storage.track_changes', true);
            $globalConfiguration->searchableAttributes = $this->getConfig('search.attributes', []);
            $globalConfiguration->onlyAcceptCommentsFromAuthenticatedUser = $this->getConfig('publishing.only_accept_comments_from_authenticated_users', false);

            // Internal parser configuration.
            $globalConfiguration->useSlimCommentPrototypeParser = $this->getConfig('internals.useFasterSlimCommentParser', false);

            // Storage directories.
            $globalConfiguration->storageDirectory = PathProvider::contentPath();
            $globalConfiguration->indexDirectory = storage_path('meerkat/index');
            $globalConfiguration->taskDirectory = storage_path('meerkat/tasks');

            // Supplemental configuration.
            $globalConfiguration->supplementMissingContent = $this->trans('parser.supplement.content');
            $globalConfiguration->supplementAuthorName = $this->trans('parser.supplement.name');
            $globalConfiguration->supplementAuthorEmail = $this->trans('parser.supplement.email');

            // Email.
            $globalConfiguration->sendEmails = $this->getConfig('email.send_mail', false);
            $globalConfiguration->onlySendEmailIfNotSpam = $this->getConfig('email.check_with_spam_guard', true);
            $globalConfiguration->addressToSendEmailTo = $this->getConfig('email.addresses', []);
            $globalConfiguration->emailFromAddress = $this->getConfig('email.from_address', null);

            foreach ($this->getConfig('authors', []) as $configSetting => $configValue) {
                $globalConfiguration->set('author_'.$configSetting, $configValue);
            }

            ConfigurationFactories::$configurationInstance = $globalConfiguration;

            return $globalConfiguration;
        });
    }

    /**
     * Registers the basic Meerkat Core dependencies.
     */
    private function registerCoreDependencies()
    {
        $this->app->singleton(ExceptionLoggerContract::class, function ($app) {
            return $app->make(ExceptionLogger::class);
        });

        $this->app->singleton(YAMLParserContract::class, function ($app) {
            return $app->make(YAMLParser::class);
        });

        $this->app->singleton(MarkdownParserContract::class, function ($app) {
            return $app->make(MarkdownParser::class);
        });

        $this->app->bind(HttpClientContract::class, Client::class);
        $this->app->bind(MailerContract::class, MeerkatMailer::class);

        $this->app->singleton(HandlerManager::class, function ($app) {
            $manager = new HandlerManager();

            $manager->registerHandler('Meerkat.spamHandler', app(SpamServiceHandler::class));
            $manager->registerHandler('Meerkat.emailHandler', app(EmailHandler::class));

            return $manager;
        });

        Statamic::booted(function () {
            DateParserFactory::$instance = app(CarbonDateParser::class);
            ExceptionLoggerFactory::$instance = app(ExceptionLoggerContract::class);
            MarkdownParserFactory::$instance = app(MarkdownParserContract::class);
        });
    }

    /**
     * Checks for the existence of the default Meerkat blueprint.
     *
     * @throws ParseException
     */
    private function checkIntegrationResourcesExist()
    {
        /** @var BlueprintProvider $blueprintProvider */
        $blueprintProvider = app(BlueprintProvider::class);

        $blueprintProvider->ensureExistence();
    }

    /**
     * Registers the Core submission handler.
     */
    private function registerSubmissionHandler()
    {
        // Invokes the submission handler on new comments, or updates.
        Meerkat::onShouldHandle(function (CommentContract $comment) {
            /** @var HandlerManager $manager */
            $manager = app(HandlerManager::class);

            $manager->handle($comment);
        });
    }

    protected function afterBoot()
    {
        Utility::make('meerkat-validation')
            ->title(trans('meerkat::general.installation_validation'))
            ->description(trans('meerkat::general.installation_validation_desc'))
            ->icon('list')
            ->view('meerkat::validation')
            ->routes(function ($router) {
                $router->post('cache', [UtilitiesController::class, 'clearSiteRoutesCache'])->name('meerkat.routes.clear.cache');
            });
    }

    protected function beforeBoot()
    {
        /** @var Manager $configurationManager */
        $configurationManager = app(Manager::class);

        // Indicate which configuration entries should be
        // made available to the Statamic installation.
        $this->config = $configurationManager->getConfigurationMap();
        $this->supplementalConfiguration = $configurationManager->getSupplementalConfigurationMap();
    }
}
