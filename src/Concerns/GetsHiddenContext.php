<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

/**
 * Trait GetsHiddenContext
 *
 * Provides helpers for retrieving thread identifiers from page contexts.
 *
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

        return $this->getCurrentContextId();
    }

    /**
     * Indicates if the current page is sharing it's Meerkat context.
     *
     * @return bool
     */
    protected function isSharingContext()
    {
        $sharing = data_get($this->context, ThreadContract::KEY_SHARE_COMMENT_CONTEXT, null);

        if ($sharing !== null && is_array($sharing) && count($sharing) > 0) {
            return true;
        }

        return false;
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
