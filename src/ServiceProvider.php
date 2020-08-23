<?php

namespace Stillat\Meerkat;

use Statamic\Statamic;
use Stillat\Meerkat\Blueprint\BlueprintProvider;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Console\Commands\MigrateCommentsCommand;
use Stillat\Meerkat\Console\Commands\StatisticsCommand;
use Stillat\Meerkat\Console\Commands\ValidateCommand;
use Stillat\Meerkat\Core\Configuration as GlobalConfiguration;
use Stillat\Meerkat\Core\ConfigurationFactories;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\FormattingConfiguration;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\Parsing\MarkdownParserFactory;
use Stillat\Meerkat\Parsing\MarkdownParser;
use Stillat\Meerkat\Parsing\YAMLParser;
use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;
use Stillat\Meerkat\Providers\DataServiceProvider;
use Stillat\Meerkat\Providers\IdentityServiceProvider;
use Stillat\Meerkat\Providers\TagsServiceProvider;
use Stillat\Meerkat\Providers\ThreadServiceProvider;
use Stillat\Meerkat\Support\Facades\Configuration;

/**
 * Class Servicerovider
 *
 * Bootstraps the core Meerkat services, configuration, and utilities.
 *
 * @package Stillat\Meerkat
 * @since 2.0.0
 */
class ServiceProvider extends AddonServiceProvider
{
    use UsesConfig;

    protected $defer = false;

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
        'web' => __DIR__ . '/../routes/web.php',
    ];

    protected $commands = [
        ValidateCommand::class,
        MigrateCommentsCommand::class,
        StatisticsCommand::class
    ];

    protected $providers = [
        /** Start: Meerkat Core Dependency Providers */
        IdentityServiceProvider::class,
        ThreadServiceProvider::class,
        DataServiceProvider::class,
        /** End: Meerkat Core Dependency Providers */

        TagsServiceProvider::class,
        ControlPanelServiceProvider::class
    ];

    protected $policies = [
    ];

    public function register()
    {
        // Registers the error log repository utilized by many Meerkat services and features.
        $this->registerMeerkatCoreErrorLogRepository();
        // Register Meerkat Core configuration containers.
        $this->registerMeerkatSpamGuardConfiguration();
        $this->registerMeerkatFormattingConfiguration(); // Global Configuration relies on the formatting config.
        $this->registerMeerkatGlobalConfiguration();
        $this->registerCoreDependencies();
        $this->checkIntegrationResourcesExist();

        parent::register();
    }

    private function registerMeerkatCoreErrorLogRepository()
    {
        $targetPath = storage_path('meerkat/logs');

        if (file_exists($targetPath) == false) {
            mkdir($targetPath, 644, true);
        }

        $this->app->singleton(ErrorCodeRepositoryContract::class, function ($app) use ($targetPath) {
            return new LocalErrorCodeRepository($targetPath);
        });

        // If we set the shared instance value, Meerkat Core can use it to report issues directly.
        LocalErrorCodeRepository::$instance = $this->app->make(ErrorCodeRepositoryContract::class);
    }

    /**
     * Creates the Meerkat Core spam service and guard configuration object.
     */
    private function registerMeerkatSpamGuardConfiguration()
    {
        // Registers the configuration for the spam Guard service and providers.
        $this->app->singleton(GuardConfiguration::class, function ($app) {
            $guardConfiguration = new GuardConfiguration();

            $guardConfiguration->autoSubmitSpamToThirdParties = $this->getConfig('publishing.auto_submit_results', false);
            $guardConfiguration->checkAgainstAllGuardServices = $this->getConfig('publishing.guard_check_all_providers', false);
            $guardConfiguration->unpublishOnGuardFailures = $this->getConfig('publishing.guard_unpublish_on_guard_failure', false);
            $guardConfiguration->bannedWords = $this->getConfig('wordlist.banned', []);

            // Set the Akismet configuration data, if available.
            foreach ($this->getConfig('akismet', []) as $configSetting => $configValue) {
                $guardConfiguration->set('akismet_' . $configSetting, $configValue);
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

            $formattingConfig->htmlTagsToClean = $this->getConfig('formatting.remove_tags', '<a><p><ul><li><ol><code><pre>');
            $formattingConfig->commentDateFormat = $this->getConfig('formatting.comment_date_format', 'Y-m-d h:m:s A');

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

            $globalConfiguration->autoPublishAnonymousPosts = $this->getConfig('publishing.auto_publish', false);
            $globalConfiguration->autoPublishAuthenticatedPosts = $this->getConfig('publishing.auto_publish_authenticated_users', false);
            $globalConfiguration->disableCommentsAfterDays = $this->getConfig('publishing.automatically_close_comments', 0);
            $globalConfiguration->trackChanges = $this->getConfig('storage.trackChanges', true);

            $globalConfiguration->storageDirectory = PathProvider::contentPath();
            $globalConfiguration->indexDirectory = storage_path('meerkat/index');

            foreach ($this->getConfig('authors', []) as $configSetting => $configValue) {
                $globalConfiguration->set('author_' . $configSetting, $configValue);
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
        $this->app->singleton(YAMLParserContract::class, function ($app) {
            return $app->make(YAMLParser::class);
        });

        $this->app->singleton(MarkdownParserContract::class, function ($app) {
            return $app->make(MarkdownParser::class);
        });

        Statamic::booted(function () {
            MarkdownParserFactory::$instance = app(MarkdownParserContract::class);
        });
    }

    private function checkIntegrationResourcesExist()
    {
        /** @var BlueprintProvider $blueprintProvider */
        $blueprintProvider = app(BlueprintProvider::class);

        $blueprintProvider->ensureExistence();
    }

    protected function beforeBoot()
    {
        // Indicate which configuration entries should be
        // made available to the Statamic installation.
        $this->config = Configuration::getConfigurationMap();
    }

}
