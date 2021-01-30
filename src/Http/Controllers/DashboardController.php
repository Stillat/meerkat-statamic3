<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\CanCorrectRoutes;
use Stillat\Meerkat\Concerns\UsesTranslations;

class DashboardController extends CpController
{
    use UsesTranslations, CanCorrectRoutes;

    public function index()
    {
        $this->attemptToCorrectRoutes();

        return view('meerkat::dashboard');
    }

    public function dashboardWithFilter($filter)
    {
        $this->attemptToCorrectRoutes();

        $validFilters = ['all', 'pending', 'published', 'spam'];

        if (!in_array($filter, $validFilters)) {
            abort(404);
            exit;
        }

        return view('meerkat::dashboard');
    }

    public function redirectToEntry($entryId, $commentId)
    {
        $statamicEntry = Entry::find($entryId);

        if ($statamicEntry === null) {
            abort(404);
        }

        $path = url($statamicEntry->url().'#comment-'.$commentId);

        return redirect($statamicEntry->url().'#comment-'.$commentId);
    }

}
