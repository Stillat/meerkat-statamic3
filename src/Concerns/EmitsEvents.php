<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

trait EmitsEvents
{

    /**
     * Emits an addon event.
     */
    protected function emitEvent($eventName, $payload)
    {
        $eventName = Addon::ADDON_NAME.'.'.$eventName;

        return event($eventName, $payload);
    }

}