<?php

namespace Stillat\Meerkat\Concerns;

use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Logging\GlobalLogState;

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
     * The last logged event name.
     *
     * @var string
     */
    protected $lastLogged = '';

    /**
     * Emits an addon event.
     *
     * @param string $eventName The name of the event.
     * @param mixed $payload The event payload/arguments.
     * @return array|null
     */
    protected function emitEvent($eventName, $payload)
    {
        $eventName = Addon::ADDON_NAME . '.' . $eventName;

        if (GlobalLogState::$isApplicationInDebugMode === true) {

            // Prevent spamming the event log.
            if ($this->lastLogged !== $eventName) {
                $eventLogPath = storage_path('meerkat/logs');
                if (!file_exists($eventLogPath)) {
                    mkdir($eventLogPath, 0755, true);
                }

                $logFilePath = storage_path('meerkat/logs/events.log');

                if (!file_exists($logFilePath)) {
                    touch($logFilePath);
                }

                file_put_contents($logFilePath, 'Firing: '.$eventName."\n", FILE_APPEND | LOCK_EX);
                $this->lastLogged = $eventName;
            }

        }

        return event($eventName, $payload);
    }

}
