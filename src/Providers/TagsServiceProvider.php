<?php

namespace Stillat\Meerkat\Providers;

use Illuminate\Foundation\Application;
use Stillat\Meerkat\Tags\Meerkat;

class TagsServiceProvider extends AddonServiceProvider
{
    protected $contexts = ['web'];

    protected $defer = false;

    protected $tags = [
      Meerkat::class
    ];

}
