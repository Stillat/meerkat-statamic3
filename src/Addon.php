<?php

namespace Stillat\Meerkat;

use Statamic\Extend\AddonRepository;

class Addon
{
    public const ADDON_NAME = 'Meerkat';
    public const CODE_ADDON_NAME = 'meerkat';
    public const ROUTE_PREFIX = 'meerkat';
    public const ADDON_ID = 'stillat/meerkat';
    public const VERSION = '0.0.1';

    /**
     * Returns an instance of Meerkat for Meerkat.
     *
     * @return \Statamic\Extend\Addon
     */
    public static function getAddon()
    {
        /** @var \Statamic\Extend\AddonRepository $addonRepository */
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

}