<?php

namespace Stillat\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Meerkat\Configuration\Manager;

/**
 * Class Configuration
 *
 * Provides a static API for the Configuration Manager instance.
 *
 * @package Stillat\Meerkat\Support\Facades
 * @since 2.0.0
 *
 * @method static array getConfigurationMap()
 * @see \Stillat\Meerkat\Configuration\Manager
 */
class Configuration extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }

}
