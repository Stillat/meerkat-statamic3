<?php

namespace Stillat\Meerkat\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;

class DashboardController extends CpController
{
    use UsesTranslations;

    public function index()
    {
        return view('meerkat::dashboard');
    }

}
