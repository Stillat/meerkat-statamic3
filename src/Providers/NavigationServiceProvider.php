<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Statamic\ControlPanel\AddonNavIcons;
use Stillat\Meerkat\Statamic\ControlPanel\Navigation;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;

/**
 * Class NavigationServiceProvider
 * @package Stillat\Meerkat\Providers
 */
class NavigationServiceProvider extends AddonServiceProvider
{

    protected $defer = true;

    protected $contexts = ['cp'];

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
        parent::boot();

        $this->addonIconInstaller->installAddonIcons(Addon::CODE_ADDON_NAME, PathProvider::getResourcesDirectory('svg'));
        $this->navigation->create();
    }

}