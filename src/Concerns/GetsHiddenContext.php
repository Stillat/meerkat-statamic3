<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

/**
 * Trait GetsHiddenContext
 *
 * Provides helpers for retrieving thread identifiers from page contexts.
 *
 * @package Stillat\Meerkat\Concerns
 * @since 2.0.0
 */
trait GetsHiddenContext
{

    /**
     * Gets the page's hidden context.
     *
     * @return string|null
     */
    protected function getHiddenContext()
    {
        $sharing = data_get($this->context, ThreadContract::KEY_SHARE_COMMENT_CONTEXT, null);

        if ($sharing !== null && is_array($sharing) && count($sharing) > 0) {
            return $sharing[0];
        }

        return data_get($this->context, 'page.id', null);
    }

    /**
     * Attempts to locate the identifier for the current context.
     *
     * @return string|null
     */
    protected function getCurrentContextId()
    {
        return data_get($this->context, 'id', null);
    }

}
