<?php

namespace Stillat\Meerkat\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\PredicateBuilder;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Feedback\SolutionProvider;
use Illuminate\Support\Facades\Event;
use Statamic\Contracts\Auth\UserRepository;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorFactoryContract;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Threads\ThreadManager;
use Stillat\Meerkat\PathProvider;

class DashboardController extends CpController
{
    use UsesTranslations;

    public function index(ThreadManagerContract $threads, DataQuery $query, CommentFilterManager $filters)
    {


        $thread = Thread::find('7ac0bdda-1b84-45f8-ac52-2575dd7e8251');
        $builder = new PredicateBuilder();
        $context = new RuntimeContext();
        $context->templateTagContext = '';
        $context->context = null;
        $context->parameters = [];


        $data = $query->withContext($context)->skip(2)->limit(2)->sortAsc(CommentContract::KEY_ID)
            ->filterBy('is:spam')->get($thread->getComments());

        dd($data);


        return view('meerkat::dashboard');
    }

}