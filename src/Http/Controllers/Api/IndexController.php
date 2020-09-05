<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Addon;

class IndexController extends CpController
{

    public function index()
    {
        return [
            'product' => Addon::ADDON_NAME,
            'version' => Addon::VERSION
        ];
    }

}
