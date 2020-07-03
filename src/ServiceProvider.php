<?php

namespace Stillat\Meerkat;

use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Providers\NavigationServiceProvider;
use Stillat\Meerkat\Providers\TagsServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $providers = [
        TagsServiceProvider::class,
        NavigationServiceProvider::class
    ];

}