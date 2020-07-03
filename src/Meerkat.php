<?php

namespace Stillat\Meerkat;

use Statamic\Extend\AddonRepository;

class Meerkat
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

        return $addonRepository->get(Meerkat::ADDON_ID);
    }


}