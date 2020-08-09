<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

class AddonNavIcons
{

    const STATAMIC_ICONS_DIRECTORY = 'vendor/statamic/cp/svg/';
    const STATAMIC_ADDON_ICONS = self::STATAMIC_ICONS_DIRECTORY . 'addons/';

    public function installAddonIcons($addonName, $iconLocation)
    {
        $isAvailable = $this->createRepository();

        if ($isAvailable) {
            $addonIconPath = public_path(AddonNavIcons::STATAMIC_ADDON_ICONS . '/' . $addonName);

            if (file_exists($addonIconPath) == false) {
                mkdir($addonIconPath);
            }

            if (file_exists($iconLocation)) {
                $svgFiles = glob($iconLocation . '/*.svg');

                if (is_array($svgFiles) && count($svgFiles) > 0) {
                    foreach ($svgFiles as $iconFile) {
                        copy($iconFile, $addonIconPath . '/' . basename($iconFile));
                    }
                }
            }
        }
    }

    protected function createRepository()
    {
        $addonIconDirectory = public_path(AddonNavIcons::STATAMIC_ADDON_ICONS);

        if (file_exists(public_path(AddonNavIcons::STATAMIC_ICONS_DIRECTORY))) {
            if (file_exists(public_path(AddonNavIcons::STATAMIC_ADDON_ICONS)) == false) {
                mkdir($addonIconDirectory);
            }
        }

        return file_exists($addonIconDirectory);
    }

}