<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\ControlPanel\AddonNavIcons;
use Stillat\Meerkat\ControlPanel\Navigation;
use Stillat\Meerkat\Meerkat;
use Stillat\Meerkat\PathProvider;

/**
 * Class NavigationServiceProvider
 * @package Stillat\Meerkat\Providers
 */
class NavigationServiceProvider extends AddonServiceProvider
{

    protected $requiresControlPanel = true;

    /**
     * The addon icon installer instance.
     *
     * @var AddonNavIcons|null
     */
    protected $addonIconInstaller = null;

    /**
     * The Meerkat Navigation helper instance.
     *
     * @var Navigation|null
     */
    protected $navigation = null;

    public function __construct(AddonNavIcons $addonIcons, Navigation $navigation)
    {
        $this->addonIconInstaller = $addonIcons;
        $this->navigation = $navigation;
    }

    public function boot()
    {
        $this->addonIconInstaller->installAddonIcons(Meerkat::CODE_ADDON_NAME, PathProvider::getResourcesDirectory('svg'));
        $this->navigation->create();
    }

}