<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Tags\Meerkat;

class TagsServiceProvider extends AddonServiceProvider
{
    protected $contexts = ['web'];

    protected $tags = [
      Meerkat::class
    ];

}
