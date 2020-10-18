<?php

namespace Stillat\Meerkat\Configuration;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Guard\SpamService;

class GuardConfigurationManager
{
    use UsesConfig;

    const KEY_GUARD_NAME = 'name';
    const KEY_GUARD_CLASS = 'class';
    const KEY_ENABLED = 'enabled';

    /**
     * The SpamService instance.
     *
     * @var SpamService
     */
    protected $spamService = null;

    public function __construct(SpamService $spamService)
    {
        $this->spamService = $spamService;
    }

    public function getConfiguration()
    {
        $configuredGuards = $this->getConfig('publishing.guards', []);
        $allDiscoveredGuards = $this->spamService->getDiscoveredGuards();
        $discoveredGuardClasses = [];

        for ($i = 0; $i < count($allDiscoveredGuards); $i += 1) {
            $guardClass = $allDiscoveredGuards[$i][self::KEY_GUARD_CLASS];

            $allDiscoveredGuards[$i][self::KEY_ENABLED] = in_array($guardClass, $configuredGuards);
            $discoveredGuardClasses[] = $guardClass;
        }

        // Has the user enabled some guards that we haven't seen yet?
        $undiscoveredGuards = array_diff($configuredGuards, $discoveredGuardClasses);

        foreach ($undiscoveredGuards as $undiscoveredGuard) {
            if (class_exists($undiscoveredGuard)) {
                // All guards should implement a static "getConfigName()" method just for this reason.
                if (method_exists($undiscoveredGuard, 'getConfigName')) {
                    $guardName = $undiscoveredGuard::getConfigName();

                    $allDiscoveredGuards[] = [
                        self::KEY_GUARD_NAME => $guardName,
                        self::KEY_GUARD_CLASS => $undiscoveredGuard,
                        self::KEY_ENABLED => true
                    ];
                }
            }
        }

        usort($allDiscoveredGuards, function ($a, $b) {
            return strcmp($a[self::KEY_GUARD_NAME], $b[self::KEY_GUARD_NAME]);
        });

        return $allDiscoveredGuards;
    }

}