<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

/**
 * Class AddonNavIcons
 *
 * Provides utilities to install custom Statamic Control Panel navigation menu icons.
 *
 * @since 2.0.0
 */
class AddonNavIcons
{
    const STATAMIC_ICONS_DIRECTORY = 'vendor/statamic/cp/svg/';

    const STATAMIC_ADDON_ICONS = self::STATAMIC_ICONS_DIRECTORY.'addons/';

    /**
     * Installs custom SVG icons into the Statamic navigation icon directory.
     *
     * Icons are installed to an addon-specific sub-directory.
     *
     * @param  string  $addonName The addon name.
     * @param  string  $iconLocation The path to the icons to install.
     */
    public function installAddonIcons($addonName, $iconLocation)
    {
        $isAvailable = $this->createRepository();

        if ($isAvailable) {
            $addonIconPath = public_path(AddonNavIcons::STATAMIC_ADDON_ICONS.'/'.$addonName);

            if (file_exists($addonIconPath) == false) {
                mkdir($addonIconPath);
            }

            if (file_exists($iconLocation)) {
                $svgFiles = glob($iconLocation.'/*.svg');

                if (is_array($svgFiles) && count($svgFiles) > 0) {
                    foreach ($svgFiles as $iconFile) {
                        copy($iconFile, $addonIconPath.'/'.basename($iconFile));
                    }
                }
            }
        }
    }

    /**
     * Handles the creation of the addon-specific icon directory.
     *
     * @return bool
     */
    protected function createRepository()
    {
        $addonIconDirectory = public_path(AddonNavIcons::STATAMIC_ADDON_ICONS);

        if (file_exists(public_path(AddonNavIcons::STATAMIC_ICONS_DIRECTORY))) {
            if (file_exists(public_path(AddonNavIcons::STATAMIC_ADDON_ICONS)) == false) {
                mkdir($addonIconDirectory);
            }
        }

        return file_exists($addonIconDirectory) && is_dir($addonIconDirectory);
    }
}
