<?php

namespace Stillat\Meerkat;

use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Providers\NavigationServiceProvider;
use Stillat\Meerkat\Providers\TagsServiceProvider;
use Stillat\Meerkat\Support\Facades\Configuration;

class ServiceProvider extends AddonServiceProvider
{

    protected $defer = false;

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $providers = [
        TagsServiceProvider::class,
        NavigationServiceProvider::class
    ];

    protected function beforeBoot()
    {
        // Indicate which configuration entries should be
        // made available to the Statamic installation.
        $this->config = Configuration::getConfigurationMap();
    }

}
