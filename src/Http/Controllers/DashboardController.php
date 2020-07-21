<?php

namespace Stillat\Meerkat\Http\Controllers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
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

    public function index(CommentStorageManagerContract $comments)
    {
        $data = $comments->getCommentsForThreadId('af43e0fb-a338-4433-b60a-3bed773be344');
        /** @var CommentContract $firstComment */
        $firstComment = array_shift($data);

        dd($firstComment->getAuthor());
        //$firstComment->setRawContent('hello, world ' . time());

//        $didSave = $comments->save($firstComment);
        //$firstComment->publish();

        dd($firstComment);

        return view('meerkat::dashboard');
    }

}