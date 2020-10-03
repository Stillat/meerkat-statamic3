<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Helpers\ThreadHelpers;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Core\Threads\Thread;

/**
 * Class ThreadIn
 *
 * Contains the thread-related default Meerkat threads.
 *
 * thread:in(list, of, ids)
 * thread:not_in(list, of, ids) - legacy alias
 * not:thread:in(list, of, ids)
 *
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 * @since 1.5.85
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class ThreadIn
{
    const FILTER_THREAD_IN = 'thread:in';
    const FILTER_THREAD_NOT_IN = 'thread:not_in';
    const FILTER_THREAD_NOT_IN_ALIAS = 'not:thread:in';
    const PARAM_THREADS = 'threads';

    /**
     * Registers the default thread:in filters.
     *
     * @param CommentFilterManager $manager The filter manager.
     */
    public function register(CommentFilterManager $manager)
    {
        $manager->filterWithTagContext(ThreadIn::FILTER_THREAD_IN, function ($comments) {
            $tempThreads = TypeConversions::parseToArray($this->get(ThreadIn::PARAM_THREADS, []));
            $threadList = ThreadHelpers::buildContextualThreadList($tempThreads, $this->getContext());

            return array_filter($comments, function (CommentContract $comment) use ($threadList) {
                return in_array($comment->getThreadId(), $threadList);
            });
        }, ThreadIn::PARAM_THREADS);

        $manager->filterWithTagContext(ThreadIn::FILTER_THREAD_NOT_IN, function ($comments) {
            $tempThreads = TypeConversions::parseToArray($this->get(ThreadIn::PARAM_THREADS, []));
            $threadList = ThreadHelpers::buildContextualThreadList($tempThreads, $this->getContext());

            return array_filter($comments, function (CommentContract $comment) use ($threadList) {
                return in_array($comment->getThreadId(), $threadList) == false;
            });
        }, ThreadIn::PARAM_THREADS);

        $manager->filterWithTagContext(ThreadIn::FILTER_THREAD_NOT_IN_ALIAS, function ($comments) {
            $tempThreads = TypeConversions::parseToArray($this->get(ThreadIn::PARAM_THREADS, []));
            $threadList = ThreadHelpers::buildContextualThreadList($tempThreads, $this->getContext());

            return array_filter($comments, function (CommentContract $comment) use ($threadList) {
                return in_array($comment->getThreadId(), $threadList) == false;
            });
        }, ThreadIn::PARAM_THREADS);
    }

}
