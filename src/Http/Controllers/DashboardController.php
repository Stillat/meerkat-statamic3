<?php

namespace Stillat\Meerkat\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorCodeRepositoryContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorLog;
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

    public function index(CommentManagerContract $manager)
    {

        dd(
          Comment::saveReplyTo('1595945413', Comment::newFromArray([
              'email' => 'test@test2.com',
              'name' => 'Hello Test',
              'comment' => 'Hello, world test test',
          ]))
        );

        Comment::replyTo('1582351200', Comment::newFromArray([
            'email' => 'test@test.com',
            'name' => 'Hello Name',
            'comment' => 'Hello, world',
        ]))->save();

        dd(Comment::find('1582351200'));
        //$comment = $manager->findById('1582351200');


        return view('meerkat::dashboard');
    }

}