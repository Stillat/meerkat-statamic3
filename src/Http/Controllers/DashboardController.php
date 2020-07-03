<?php

namespace Stillat\Meerkat\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index()
    {
        return view('meerkat::dashboard');
    }

}