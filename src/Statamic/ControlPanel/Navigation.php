<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

use Statamic\Facades\CP\Nav;

/**
 * Class Navigation
 *
 * Manages interactions between Meerkat and the Statamic Control Panel navigation menu.
 *
 * @package Stillat\Meerkat\ControlPanel
 * @since 2.0.0
 */
class Navigation
{

    /**
     * Creates the addon's navigation menu.
     */
    public function create()
    {
        Nav::extend(function ($nav) {

            $nav->create('Comments')
                ->section('Content')
                ->icon('addons/meerkat/chat-bubble-dots')
                ->route('cp.meerkat.dashboard');

        });
    }

}
