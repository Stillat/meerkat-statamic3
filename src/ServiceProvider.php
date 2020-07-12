<?php

namespace Stillat\Meerkat;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\FormattingConfiguration;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Configuration as GlobalConfiguration;
use Stillat\Meerkat\Parsing\MarkdownParser;
use Stillat\Meerkat\Parsing\YAMLParser;
use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;
use Stillat\Meerkat\Providers\IdentityServiceProvider;
use Stillat\Meerkat\Providers\TagsServiceProvider;
use Stillat\Meerkat\Providers\ThreadServiceProvider;
use Stillat\Meerkat\Support\Facades\Configuration;

class ServiceProvider extends AddonServiceProvider
{
    use UsesConfig;

    protected $defer = false;

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $providers = [
        /** Start: Meerkat Core Dependency Providers */
        IdentityServiceProvider::class,
        ThreadServiceProvider::class,
        /** End: Meerkat Core Dependency Providers */

        TagsServiceProvider::class,
        ControlPanelServiceProvider::class
    ];

    protected $policies = [
    ];

    protected function beforeBoot()
    {
        // Indicate which configuration entries should be
        // made available to the Statamic installation.
        $this->config = Configuration::getConfigurationMap();
    }

    public function register()
    {
        // Register Meerkat Core configuration containers.
        $this->registerMeerkatSpamGuardConfiguration();
        $this->registerMeerkatFormattingConfiguration(); // Global Configuration relies on the formatting config.
        $this->registerMeerkatGlobalConfiguration();
        $this->registerCoreDependencies();

        parent::register();
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
            foreach($this->getConfig('akismet', []) as $configSetting => $configValue) {
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
            $globalConfiguration->storageDirectory = PathProvider::contentPath();

            foreach ($this->getConfig('authors', []) as $configSetting => $configValue) {
                $globalConfiguration->set('author_'.$configSetting, $configValue);
            }

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
    }
}
