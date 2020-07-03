<?php

namespace Stillat\Meerkat\ControlPanel;

use Statamic\Facades\CP\Nav;

/**
 * Class Navigation
 * @package Stillat\Meerkat\ControlPanel
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