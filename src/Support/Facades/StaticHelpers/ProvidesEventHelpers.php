<?php

namespace Stillat\Meerkat\Support\Facades\StaticHelpers;

use Illuminate\Support\Facades\Event;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;

trait ProvidesEventHelpers
{


    public static function onRegisteringControlPanel(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, $handler);
    }

}
