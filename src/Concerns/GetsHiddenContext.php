<?php

namespace Stillat\Meerkat\Concerns;

trait GetsHiddenContext
{
    protected function getHiddenContext()
    {
        $sharing = data_get($this->context, 'meerkat_share_comments', null);

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