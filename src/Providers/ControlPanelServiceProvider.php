<?php

namespace Stillat\Meerkat\Providers;

use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\EmitsEvents;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Statamic\ControlPanel\AddonNavIcons;
use Stillat\Meerkat\Statamic\ControlPanel\Navigation;

/**
 * Class ControlPanelServiceProvider
 *
 * Registers various Meerkat features that interact directly with the Statamic Control Panel.
 *
 * @since 2.0.0
 */
class ControlPanelServiceProvider extends AddonServiceProvider
{
    const EVENT_REGISTERING_CONTROL_PANEL = 'registering.controlPanel';

    protected $contexts = ['cp'];

    use EmitsEvents;

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
        require_once PathProvider::getAddonDirectory('extend/default.php');

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

        $currentLocale = config('app.locale', 'en');

        Statamic::style('meerkat', Addon::VERSION.'/meerkat');
        Statamic::script('meerkat', Addon::VERSION.AddonServiceProvider::getResourceJavaScriptPath('/meerkatExtend'));
        Statamic::script('meerkat', Addon::VERSION.AddonServiceProvider::getResourceJavaScriptPath('/'.$currentLocale.'_translations'));
        $this->emitEvent(ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, '');
        Statamic::script('meerkat', Addon::VERSION.AddonServiceProvider::getResourceJavaScriptPath('/meerkat'));
        Statamic::script('meerkat', Addon::VERSION.AddonServiceProvider::getResourceJavaScriptPath('/bootstrap'));
    }
}
