<?php

namespace Stillat\Meerkat\Http\Controllers;

use App\Http\Controllers\Controller;
use Stillat\Meerkat\Concerns\UsesTranslations;

class DashboardController extends Controller
{
    use UsesTranslations;

    public function index()
    {
        return view('meerkat::dashboard');
    }

}