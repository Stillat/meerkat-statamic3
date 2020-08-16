<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class IsFilters
 *
 * Contains the is:<property> related filters.
 *
 * @package Stillat\Meerkat\Core\Data\Filters\DefaultFilters
 * @since 1.5.85
 *
 * @method mixed get($key, $default = null) Gets a filter parameter value.
 * @method mixed getContext() Gets the filter context.
 * @see CommentFilter
 */
class IsFilters
{

    const PARAM_COMPARISON = 'comparison';
    const PARAM_TIMESTAMP = 'timestamp';
    const PARAM_RANGE = 'range';

    public function register(CommentFilterManager $manager)
    {
        $manager->filterWithTagContext('is:before', function ($comments) {
            // TODO: Implement "before timestamp".
        }, IsFilters::PARAM_TIMESTAMP);

        $manager->filterWithTagContext('is:after', function ($comments) {
            // TODO: Implement "after timestamp"
        }, IsFilters::PARAM_TIMESTAMP);

        $manager->filterWithTagContext('is:between', function ($comments) {
            // TODO: Implement "between timestamp"
        }, IsFilters::PARAM_RANGE);

        $manager->filterWithTagContext('is:spam', function ($comments) {
            $includeSpam = TypeConversions::getBooleanValue($this->get(IsFilters::PARAM_COMPARISON, false));

            return array_filter($comments, function (CommentContract $comment) use ($includeSpam) {
                $isSpam = $comment->isSpam();

                if ($includeSpam) {
                    if ($isSpam) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($isSpam) {
                        return false;
                    }
                }

                return true;
            });
        }, IsFilters::PARAM_COMPARISON);

        $manager->filterWithTagContext('is:published', function ($comments) {
            $includePublished = TypeConversions::getBooleanValue($this->get(IsFilters::PARAM_COMPARISON, true));

            return array_filter($comments, function (CommentContract $comment) use ($includePublished) {
                $isPublished = $comment->published();

                if ($includePublished) {
                    if ($isPublished) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ($isPublished) {
                        return false;
                    }
                }

                return true;
            });
        }, IsFilters::PARAM_COMPARISON);
    }

}