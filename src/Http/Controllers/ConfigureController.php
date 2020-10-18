<?php

namespace Stillat\Meerkat\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Auth\UserGroupRepository;
use Statamic\Http\Controllers\CP\CpController;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Configuration\ConfigurationItem;
use Stillat\Meerkat\Configuration\GuardConfigurationManager;
use Stillat\Meerkat\Configuration\Manager;
use Stillat\Meerkat\Configuration\UserConfigurationManager;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Guard\Providers\AkismetSpamGuard;
use Stillat\Meerkat\Core\Http\Responses\Responses;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Support\Arr;
use Stillat\Meerkat\Http\RequestHelpers;
use Stillat\Meerkat\Permissions\GroupPermissionsManager;

class ConfigureController extends CpController
{
    use UsesConfig, UsesTranslations;

    const KEY_CONFIG_HASH = 'change_set';
    const KEY_CONFIG_ITEMS = 'config';
    const KEY_HAS_MANAGED = 'has_managed';
    const KEY_SPAM_GUARDS = 'spam_guards';
    const KEY_PERMISSIONS = 'permissions';
    const KEY_PARAM_API_KEY = 'api_key';
    const KEY_PARAM_FRONT_PAGE = 'front_page';
    const KEY_PARAM_PER_PAGE = 'per_page';

    const KEY_SETTINGS = 'settings';
    const KEY_ITEMS = 'items';
    const KEY_USER_SETTINGS = 'user';
    const KEY_USER_PER_PAGE = 'perPage';
    const KEY_USER_AVATAR = 'avatar';

    const KEY_RETURN_SETTINGS_UPDATED = 'settings_updated';
    const KEY_RETURN_USER_SETTINGS_UPDATED = 'preferences_updated';

    public function index()
    {
        $addonsUrl = url(config('statamic.cp.route'), 'addons');
        $commentsUrl = url(config('statamic.cp.route'), 'meerkat');

        return view('meerkat::settings', [
            'user' => Auth::user(),
            'addonUrl' => $addonsUrl,
            'commentsUrl' => $commentsUrl
        ]);
    }

    public function updateUserPerPage(UserConfigurationManager $userConfigurationManager)
    {
        $options = [10, 25, 50, 100];
        $perPage = intval(request()->get(self::KEY_PARAM_PER_PAGE, 25));

        if (!in_array($perPage, $options)) {
            $perPage = 25;
        }

        $saved = $userConfigurationManager->updateConfiguration($perPage, $userConfigurationManager->getAvatarDriver());

        if ($saved === true) {
            return Responses::generalSuccess();
        }

        return Responses::generalFailure();
    }

    public function getCurrentConfigHash(Manager $configurationManager)
    {
        try {
            return Responses::successWithData([
                self::KEY_CONFIG_HASH => $configurationManager->getConfigurationHash()
            ]);
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
            return Responses::generalFailure();
        }
    }

    public function validateAkismetApiKey(AkismetSpamGuard $guard)
    {
        $apiKey = request()->get(self::KEY_PARAM_API_KEY, null);
        $frontPage = request()->get(self::KEY_PARAM_FRONT_PAGE, null);

        if ($apiKey === null || $frontPage === null || mb_strlen(trim($apiKey)) === 0 || mb_strlen(trim($frontPage)) === 0) {
            return Responses::failureWithData([
                'message' => $this->trans('config.validate_akismet_no_params')
            ]);
        }

        $success = false;

        try {
            $success = $guard->checkConfigurationKey(trim($apiKey), trim($frontPage));
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
            return Responses::failureWithData([
                'message' => $this->trans('config.validate_akismet_failure')
            ]);
        }

        if ($success === true) {
            return Responses::successWithData([
                'message' => $this->trans('config.validate_akismet_okay')
            ]);
        }

        return Responses::failureWithData([
            'message' => $this->trans('config.validate_akismet_api_invalid')
        ]);
    }

    public function save(Manager $configManager, UserConfigurationManager $userConfigurationManager, UserGroupRepository $userGroups)
    {
        RequestHelpers::setActionFromRequest($this->request);
        $requestData = request()->all();

        if (!array_key_exists(self::KEY_SETTINGS, $requestData)) {
            return Responses::recoverableFailure(Errors::CONFIG_MISSING_PARAMETERS);
        }

        $requestData = $requestData[self::KEY_SETTINGS];

        $savedConfiguration = false;
        $preferencesUpdated = false;

        if ($this->getConfig('permissions.control_panel_config', true) === true) {
            try {
                if ($userConfigurationManager->isSysAdmin() === true && array_key_exists(self::KEY_ITEMS, $requestData)) {

                    $statamicGroups = $userGroups->all();
                    $validGroups = [];

                    foreach ($statamicGroups as $group) {
                        $validGroups[] = $group->handle();
                    }

                    unset($statamicGroups);

                    $configManager->setValidGroups($validGroups);

                    // Convert the incoming settings.
                    $configItems = $requestData[self::KEY_ITEMS];

                    if ($configItems !== null && is_array($configItems)) {
                        $configItems = array_values($configItems);
                        $items = [];

                        foreach ($configItems as $item) {
                            $items[] = ConfigurationItem::fromArray($item);
                        }

                        unset($configItems);

                        $savedConfiguration = $configManager->save($items);
                    }
                } else {
                    $savedConfiguration = true;
                }
            } catch (Exception $e) {
                return Responses::generalFailure();
            }
        } else {
            $savedConfiguration = true;
        }

        $userSettings = $requestData[self::KEY_USER_SETTINGS];

        if ($userSettings !== null && Arr::matches([self::KEY_USER_PER_PAGE, self::KEY_USER_AVATAR], $userSettings)) {
            $preferencesUpdated = $userConfigurationManager->updateConfiguration(intval($userSettings[self::KEY_USER_PER_PAGE]), $userSettings[self::KEY_USER_AVATAR]);
        }

        return Responses::conditionalWithData($savedConfiguration && $preferencesUpdated, [
            self::KEY_RETURN_SETTINGS_UPDATED => $savedConfiguration,
            self::KEY_RETURN_USER_SETTINGS_UPDATED => $preferencesUpdated
        ]);
    }

    public function getConfiguration(Manager $configManager, GroupPermissionsManager $permissionsManager, GuardConfigurationManager $guardConfig)
    {
        return Responses::successWithData([
            self::KEY_CONFIG_HASH => $configManager->getConfigurationHash(),
            self::KEY_CONFIG_ITEMS => $configManager->getConfigurationItemArray(),
            self::KEY_HAS_MANAGED => $configManager->hasManagedItems(),
            self::KEY_SPAM_GUARDS => $guardConfig->getConfiguration(),
            self::KEY_PERMISSIONS => $permissionsManager->getPermissionMapping()
        ]);
    }


}