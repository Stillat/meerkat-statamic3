<?php

namespace Stillat\Meerkat;

use Statamic\Extend\AddonRepository;

/**
 * Class Addon
 *
 * Provides basic addon identification details and utilities.
 *
 * @since 2.0.0
 */
class Addon
{
    public const ADDON_NAME = 'Meerkat';

    public const CODE_ADDON_NAME = 'meerkat';

    public const ROUTE_PREFIX = 'meerkat';

    public const ADDON_ID = 'stillat/meerkat';

    public const VERSION = '2.4.14-dev';

    /**
     * Gets the addon API prefix.
     *
     * @return string
     */
    public static function getApiPrefix()
    {
        return 'mapi/'.Addon::CODE_ADDON_NAME;
    }

    /**
     * Returns an instance of Meerkat for Meerkat.
     *
     * @return \Statamic\Extend\Addon
     */
    public static function getAddon()
    {
        /** @var AddonRepository $addonRepository */
        $addonRepository = app(AddonRepository::class);

        return $addonRepository->get(Addon::ADDON_ID);
    }

    /**
     * Gets the Addon's namespace without waiting for the Statamic manifest.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * Indicates if the Addon is running on Windows.
     *
     * @return bool
     */
    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
