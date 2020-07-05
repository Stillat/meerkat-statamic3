<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Statamic\ControlPanel\AddonNavIcons;
use Stillat\Meerkat\Statamic\ControlPanel\Navigation;

/**
 * Class NavigationServiceProvider
 * @package Stillat\Meerkat\Providers
 */
class ControlPanelServiceProvider extends AddonServiceProvider
{

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

    /**
     * The language translation patcher instance.
     *
     * @var \Stillat\Meerkat\Translation\LanguagePatcher|null
     */
    protected $languagePatcher = null;

    public function __construct(AddonNavIcons $addonIcons, Navigation $navigation)
    {
        parent::__construct(app());
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