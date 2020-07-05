<?php

namespace Stillat\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Meerkat\Configuration\Manager;

/**
 * Class Configuration
 * @package Stillat\Meerkat\Support\Facades
 *
 * @method static array getConfigurationMap()
 *
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