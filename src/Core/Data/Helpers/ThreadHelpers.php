<?php

namespace Stillat\Meerkat\Core\Data\Helpers;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilter;

/**
 * Class ThreadHelpers
 *
 * Provides utilities for retrieving contextual information about threads.
 *
 * @package Stillat\Meerkat\Core\Data\Helpers
 * @since 2.0.0
 */
class ThreadHelpers
{

    /**
     * Generates a list of thread identifiers from the provided context.
     *
     * @param array $knownIds A list of known thread identifiers.
     * @param mixed|null $context An optional thread context.
     * @return array
     */
    public static function buildContextualThreadList($knownIds, $context = null)
    {
        $threadList = [];

        foreach ($knownIds as $id) {
            if ($id === CommentFilter::PARAM_GLOBAL_CURRENT) {
                if ($context !== null) {
                    $threadId = self::getThreadId($context);

                    if ($threadId !== null) {
                        $threadList[] = $threadId;
                    }
                }
            } else {
                $threadList[] = $id;
            }
        }

        return $threadList;
    }

    /**
     * Attempts to locate a thread's identifier from the provided context.
     *
     * @param mixed $context The context to search in.
     * @return string|null
     */
    public static function getThreadId($context)
    {
        if (is_array($context)) {
            if (array_key_exists(ThreadContract::KEY_ID, $context)) {
                return $context[ThreadContract::KEY_ID];
            }
        }

        if (is_object($context)) {
            if (method_exists($context, 'getId')) {
                return $context->getId();
            }

            if (property_exists($context, ThreadContract::KEY_ID)) {
                return $context[ThreadContract::KEY_ID];
            }
        }

        return null;
    }

}