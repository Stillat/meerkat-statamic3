<?php

namespace Stillat\Meerkat\Statamic\ControlPanel;

use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Configuration\UserConfigurationManager;
use Stillat\Meerkat\Core\Contracts\Identity\IdentityManagerContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\PathProvider;

/**
 * Class SettingsProvider
 *
 * Provides utilities for gathering JavaScript variable names and values.
 *
 * @package Stillat\Meerkat\Statamic\ControlPanel
 * @since 2.0.0
 */
class SettingsProvider
{
    use UsesConfig;

    const JS_NAME_AVATAR_DRIVER = 'avatarDriver';
    const JS_NAME_CP_CONFIG_ENABLED = 'controlPanelConfigurationEnabled';
    const JS_NAME_TELEMETRY_ENABLED = 'telemetryEnabled';

    /**
     * The SanitationManagerContract implementation instance.
     *
     * @var SanitationManagerContract
     */
    protected $sanitationManager = null;

    /**
     * The IdentityManagerContract implementation instance.
     *
     * @var IdentityManagerContract
     */
    protected $identityManager = null;

    /**
     * The UserConfigurationManager instance.
     *
     * @var UserConfigurationManager
     */
    protected $userConfigurationManager = null;

    public function __construct(SanitationManagerContract $sanitationManager,
                                IdentityManagerContract $identityManager,
                                UserConfigurationManager $userConfigurationManager)
    {
        $this->sanitationManager = $sanitationManager;
        $this->identityManager = $identityManager;
        $this->userConfigurationManager = $userConfigurationManager;
    }

    /**
     * Creates a JavaScript snippet that can be utilized to provide the Meerkat CoreJS runtime with server-side settings.
     *
     * @return string
     */
    public function emitStatements()
    {
        $userSettings = $this->userConfigurationManager->getConfiguration();

        if (is_null($userSettings) || is_array($userSettings) === false) {
            $userSettings = [];
            $userSettings['cp_per_page'] = 10;
            $userSettings['cp_avatar_driver'] = 'initials';
        }

        // Add the user's email address to the user settings information.
        $userSettings['email'] = $this->userConfigurationManager->getEmailAddress();
        $userSettings['isSuper'] = $this->userConfigurationManager->isSysAdmin();

        $userSettings = json_encode($userSettings);

        $jsonPermissionSet = json_encode($this->identityManager->getIdentityContext()->getPermissionSet());

        $javaScriptStub = file_get_contents(PathProvider::getStub('settings.js'));
        $settingAssignments = [];

        foreach ($this->getSettings() as $settingName => $value) {
            $settingAssignments[] = 'window.meerkat.Config.Environment.Settings[\'' . $settingName . '\'] = \'' . $value . '\';';
        }

        $settings = join(';', $settingAssignments);

        $javaScriptStub = str_replace('/*user-settings*/', 'window.meerkat.Config.Environment.UserPreferences = ' . $userSettings . ';', $javaScriptStub);
        $javaScriptStub = str_replace('/*settings*/', $settings, $javaScriptStub);
        $javaScriptStub = str_replace('/*usercontext*/', 'window.meerkat.Config.Environment.UserContext = ' . $jsonPermissionSet . ';', $javaScriptStub);

        return $javaScriptStub;
    }

    /**
     * Returns a mapping of JavaScript variable names and their sanitized values.
     *
     * @return array
     */
    public function getSettings()
    {
        $avatarDriver = $this->sanitationManager->sanitize(
            $this->getConfig('authors.cp_avatar_driver', 'initials')
        );

        return [
            SettingsProvider::JS_NAME_CP_CONFIG_ENABLED => $this->getConfig('permissions.control_panel_config', true),
            SettingsProvider::JS_NAME_AVATAR_DRIVER => $avatarDriver,
            SettingsProvider::JS_NAME_TELEMETRY_ENABLED => $this->getConfig('telemetry.enabled')
        ];
    }

}
