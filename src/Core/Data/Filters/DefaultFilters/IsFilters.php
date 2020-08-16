<?php

namespace Stillat\Meerkat\Core\Data\Filters\DefaultFilters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Exceptions\FilterException;
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
            $beforeTimestamp = intval($this->get(IsFilters::PARAM_TIMESTAMP));

            return array_filter($comments, function (CommentContract $comment) use ($beforeTimestamp) {
                return intval($comment->getId()) <= $beforeTimestamp;
            });
        }, IsFilters::PARAM_TIMESTAMP);

        $manager->filterWithTagContext('is:after', function ($comments) {
            $sinceTimestamp = intval($this->get(IsFilters::PARAM_TIMESTAMP));

            return array_filter($comments, function (CommentContract $comment) use ($sinceTimestamp) {
               return intval($comment->getId()) >= $sinceTimestamp;
            });
        }, IsFilters::PARAM_TIMESTAMP);

        $manager->filterWithTagContext('is:between', function ($comments) {
            $range = TypeConversions::parseToArray($this->get(IsFilters::PARAM_RANGE), ',');

            if (count($range) === 2) {
                $sinceTimestamp = intval($range[0]);
                $beforeTimestamp = intval($range[1]);

                return array_filter($comments, function (CommentContract $comment) use ($sinceTimestamp, $beforeTimestamp) {
                    $commentDateValue = intval($comment->getId());

                    return ($commentDateValue >= $sinceTimestamp && $commentDateValue <= $beforeTimestamp);
                });
            }

            throw new FilterException('is:between requires two parameters: '.count($range). ' given.');
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