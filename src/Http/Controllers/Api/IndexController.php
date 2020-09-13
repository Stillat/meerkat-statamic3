<?php

namespace Stillat\Meerkat\Http\Controllers\Api;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Http\Responses\Responses;

class IndexController extends CpController
{

    public function index()
    {
        return Responses::successWithData([
            'product' => Addon::ADDON_NAME,
            'version' => Addon::VERSION
        ]);
    }

}
