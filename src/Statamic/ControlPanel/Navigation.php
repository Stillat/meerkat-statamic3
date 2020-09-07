<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

use Statamic\Facades\CP\Nav;
use Stillat\Meerkat\Concerns\UsesTranslations;

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
    use UsesTranslations;

    /**
     * Creates the addon's navigation menu.
     */
    public function create()
    {
        Nav::extend(function ($nav) {

            $nav->create($this->trans('display.nav_comments'))
                ->section('Content')
                ->icon('addons/meerkat/meerkat-nav')
                ->route('cp.meerkat.dashboard');

        });
    }

}
