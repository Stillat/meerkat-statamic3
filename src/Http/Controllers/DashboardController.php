<?php

namespace Stillat\Meerkat\Http\Controllers;

use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Feedback\SolutionProvider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use Statamic\Contracts\Auth\UserRepository;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Threads\ThreadManager;
use Stillat\Meerkat\PathProvider;

class DashboardController extends Controller
{
    use UsesTranslations;

    public function index(ThreadManager $manager, SolutionProvider $solutionProvider, ErrorCodeRepositoryContract $repo)
    {
        $thread1 = $manager->findById('af43e0fb-a338-4433-b60a-3bed773be341', false);

        $manager->restoreThread('af43e0fb-a338-4433-b60a-3bed773be341');
        $thread2 = $manager->findById('af43e0fb-a338-4433-b60a-3bed773be341', false);



        dd($thread1, $thread2);

        return view('meerkat::dashboard');
    }

}