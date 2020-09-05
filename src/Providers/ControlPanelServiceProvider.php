<?php

namespace Stillat\Meerkat\Providers;

use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Statamic\ControlPanel\AddonNavIcons;
use Stillat\Meerkat\Statamic\ControlPanel\Navigation;
use Stillat\Meerkat\Translation\LanguagePatcher;

/**
 * Class ControlPanelServiceProvider
 *
 * Registers various Meerkat features that interact directly with the Statamic Control Panel.
 *
 * @package Stillat\Meerkat\Providers
 * @since 2.0.0
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
     * @var LanguagePatcher|null
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

        $this->addonIconInstaller->installAddonIcons(
            Addon::CODE_ADDON_NAME,
            PathProvider::getResourcesDirectory('svg')
        );

        $this->navigation->create();

        Statamic::script('meerkat', Addon::VERSION . '/meerkatExtend');
        Statamic::script('meerkat', Addon::VERSION . '/meerkat');
        Statamic::script('meerkat', Addon::VERSION . '/bootstrap');
    }

}
