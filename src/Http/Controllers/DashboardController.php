<?php

namespace Stillat\Meerkat\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Data\DataSetContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentChangeSetStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\PredicateBuilder;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Threads\Thread;

class DashboardController extends CpController
{
    use UsesTranslations;

    public function index(CommentStorageManagerContract $comments, ThreadManagerContract $threads, DataQuery $query, CommentFilterManager $filters, CommentChangeSetStorageManagerContract $changeSets)
    {

        dd(Comment::find('1597524673')->getRevisionCount(), 'asdf');
/*

        $comment1 = Comment::find('1597524673');

        $comment = Comment::find('1597524673');

        $comment->setDataAttribute('another_test2', 'testinasdfasdfg things!');
        $comment->setDataAttribute(CommentContract::KEY_PUBLISHED, true);
        // $comment->setDataAttribute('asdf', 'asdfasdf');
        $comment->save();

        $comment2 = Comment::find('1597524673');

        dd('adsf',$comment1, $comment2);*/

/**
        $thread = Thread::find('7ac0bdda-1b84-45f8-ac52-2575dd7e8251');
        $builder = new PredicateBuilder();
        $context = new RuntimeContext();
        $context->templateTagContext = '';
        $context->context = null;
        $context->parameters = [];

        dd($thread->getComments());

  */      /**
         *
         * ->nameAllGroups('date_groups')
         *
         * ->skip(6)->limit(5)
         * ->nameAllGroups('date_groups')->groupName('date_group')
         * ->collectionName('comments')->groupBy('group:date', function (CommentContract $comment) {
         * $comment->setDataAttribute('group:date', $comment->getCommentDate()->format('Y m, d'));
             * })
         */

        /** @var DataSetContract $data */
        $data = $query->withContext($context)->limit(5)->nameAllGroups('date_groups')->groupName('date_group')
            ->collectionName('comments')->groupBy('group:date', function ($comment) {
                $comment->setDataAttribute('group:date', $comment->getCommentDate()->format('Y m, d'));
            })->searchFor('kristof')->get($thread->getComments());


        dd($data);
        dd('asdf222', $data);


        return view('meerkat::dashboard');
    }

}
