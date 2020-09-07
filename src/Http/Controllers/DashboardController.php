<?php

namespace Stillat\Meerkat\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesTranslations;

class DashboardController extends CpController
{
    use UsesTranslations;

    public function index()
    {
        return view('meerkat::dashboard');
    }

}
