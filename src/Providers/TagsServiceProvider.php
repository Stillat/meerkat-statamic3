<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Tags\Meerkat;

/**
 * Class TagsServiceProvider
 *
 * Manages the registration of Meerkat specific Antlers tags.
 *
 * @since 2.0.0
 */
class TagsServiceProvider extends AddonServiceProvider
{
    protected $defer = false;

    protected $tags = [
        Meerkat::class,
    ];
}
