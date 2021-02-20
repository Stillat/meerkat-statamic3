<?php

namespace Stillat\Meerkat\Configuration;

use Exception;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Auth\UserRepository;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Contracts\Configuration\UserConfigurationStorageManagerContract;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Support\Arr;
use Throwable;

/**
 * Class UserConfigurationManager
 *
 * Manages Statamic user-specific Control Panel settings.
 *
 * @package Stillat\Meerkat\Configuration
 * @since 2.1.0
 */
class UserConfigurationManager
{
    use UsesConfig;

    const KEY_AVATAR_DRIVER = 'cp_avatar_driver';
    const KEY_PER_PAGE = 'cp_per_page';

    /**
     * The UserRepository implementation instance.
     *
     * @var UserRepository
     */
    protected $users = null;

    /**
     * The currently authenticated Statamic user.
     *
     * @var User|null
     */
    protected $currentUser = null;

    /**
     * Indicates if the configuration manager has loaded the current user details.
     *
     * @var bool
     */
    protected $hasLoadedUser = false;

    /**
     * The current user's system identifier.
     *
     * @var string|null
     */
    protected $userId = null;

    /**
     * The UserConfigurationStorageManagerContract implementation instance.
     *
     * @var UserConfigurationStorageManagerContract
     */
    protected $userConfigStorageManager = null;

    public function __construct(UserRepository $userRepository, UserConfigurationStorageManagerContract $userConfigManager)
    {
        $this->users = $userRepository;
        $this->userConfigStorageManager = $userConfigManager;
    }

    /**
     * Gets the current user's configured avatar driver.
     *
     * @return string
     */
    public function getAvatarDriver()
    {
        $config = $this->getConfiguration();

        return $config[self::KEY_AVATAR_DRIVER];
    }

    /**
     * Attempts to return the current user's specific configuration.
     *
     * If a failure is encountered, a default set of configuration values will be returned.
     *
     * @return array
     */
    public function getConfiguration()
    {
        $this->loadUserDetails();

        if ($this->currentUser === null) {
            return $this->getDefaultConfiguration();
        }

        // If, for some reason, the configuration path does not exist, let's return the defaults.
        // The user specific settings are not important enough to break everything over.
        if ($this->userConfigStorageManager->hasUserConfiguration($this->userId) === false) {
            return $this->getDefaultConfiguration();
        }

        try {
            $userConfiguration = $this->userConfigStorageManager->getConfiguration($this->userId);

            if ($userConfiguration !== null && is_array($userConfiguration) && Arr::matches([
                    self::KEY_PER_PAGE, self::KEY_AVATAR_DRIVER
                ], $userConfiguration)) {
                return $userConfiguration;
            }
        } catch (Exception $e) {
            // Again, user specific settings shouldn't bring down the site.
            ExceptionLoggerFactory::log($e);
        }

        return $this->getDefaultConfiguration();
    }

    /**
     * Attempts to load the currently authenticated user details.
     */
    protected function loadUserDetails()
    {
        if ($this->hasLoadedUser === true) {
            return;
        }

        try {
            $this->hasLoadedUser = true;

            $this->currentUser = $this->users->current();

            if ($this->currentUser === null) {
                $this->userId = null;
            }

            $this->userId = $this->currentUser->id();

            if (!$this->userConfigStorageManager->hasUserConfiguration($this->userId)) {
                $this->userConfigStorageManager->saveConfiguration($this->userId, $this->getDefaultConfiguration());
            }
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
        } catch (Throwable $t) {
            ErrorReporterFactory::report($t);
        }
    }

    /**
     * Generates the default user specific Control Panel configuration.
     *
     * @return array
     */
    private function getDefaultConfiguration()
    {
        $defaultAvatarDriver = $this->getConfig('authors.cp_avatar_driver', 'initials');

        return [
            self::KEY_AVATAR_DRIVER => $defaultAvatarDriver,
            self::KEY_PER_PAGE => 25
        ];
    }

    /**
     * Indicates if the current user is a Statamic "Super User".
     *
     * @return bool
     */
    public function isSysAdmin()
    {
        $this->loadUserDetails();

        if ($this->currentUser === null) {
            return false;
        }

        return $this->currentUser->isSuper();
    }

    /**
     * Updates the user specific configuration values.
     *
     * @param int $perPage The number of items to load per page.
     * @param string $avatarDriver The user's avatar driver.
     * @return bool
     */
    public function updateConfiguration($perPage, $avatarDriver)
    {
        $this->loadUserDetails();

        if ($this->currentUser === null) {
            return false;
        }

        $settings = [
            self::KEY_AVATAR_DRIVER => $avatarDriver,
            self::KEY_PER_PAGE => $perPage
        ];

        try {
            return $this->userConfigStorageManager->saveConfiguration($this->userId, $settings);
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
        }

        return true;
    }

    /**
     * Attempts to retrieve the current user's email address.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        if ($this->currentUser === null) {
            return 'example@example.org';
        }

        return $this->currentUser->email();
    }

}
