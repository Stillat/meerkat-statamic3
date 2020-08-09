<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;

/**
 * Trait EmitsEvents
 *
 * Provides helpers for emitting addon events.
 *
 * @package Stillat\Meerkat\Concerns
 * @since 2.0.0
 */
trait EmitsEvents
{

    /**
     * Emits an addon event.
     */
    protected function emitEvent($eventName, $payload)
    {
        $eventName = Addon::ADDON_NAME . '.' . $eventName;

        return event($eventName, $payload);
    }

}
