<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Tags\Meerkat;

class TagsServiceProvider extends AddonServiceProvider
{

    protected $defer = false;

    protected $tags = [
      Meerkat::class
    ];

}
