<?php

namespace Stillat\Meerkat\Configuration;

use Exception;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Facades\YAML;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Support\Arr;

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
     * The user specific configuration path.
     *
     * @var string
     */
    protected $configurationPath = '';

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

    public function __construct(UserRepository $userRepository)
    {
        $this->users = $userRepository;
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

        // If, for some reason, the configuration path does not exist, let's return the defaults.
        // The user specific settings are not important enough to break everything over.
        if (!file_exists($this->configurationPath)) {
            return $this->getDefaultConfiguration();
        }

        try {
            $contents = file_get_contents($this->configurationPath);
            $parsedContents = YAML::parse($contents);

            if ($parsedContents !== null && is_array($parsedContents) && Arr::matches([
                    self::KEY_PER_PAGE, self::KEY_AVATAR_DRIVER
                ], $parsedContents)) {
                return $parsedContents;
            }
        } catch (Exception $e) {
            // Again, user specific settings shouldn't bring down the site.
            ExceptionLoggerFactory::log($e);
        }

        return $this->getDefaultConfiguration();
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
     * Attempts to load the currently authenticated user details.
     */
    protected function loadUserDetails()
    {
        if ($this->hasLoadedUser === true) {
            return;
        }

        $this->hasLoadedUser = true;

        $this->currentUser = $this->users->current();
        $this->userId = $this->currentUser->id();
        $this->configurationPath = config_path('meerkat/users/' . $this->userId . '.yaml');

        if (!file_exists($this->configurationPath)) {
            $this->writeDefaultFile($this->configurationPath);
        }
    }

    /**
     * Indicates if the current user is a Statamic "Super User".
     *
     * @return bool
     */
    public function isSysAdmin()
    {
        $this->loadUserDetails();

        return $this->currentUser->isSuper();
    }

    /**
     * Attempts to write the default user-specific configuration file to disk.
     *
     * @param string $path The storage path.
     * @return bool
     */
    private function writeDefaultFile($path)
    {
        $settingContent = YAML::dump($this->getDefaultConfiguration());

        $results = file_put_contents($path, $settingContent);

        if ($results === false) {
            return false;
        }

        return true;
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
     * Updates the user specific configuration values.
     *
     * @param int $perPage The number of items to load per page.
     * @param string $avatarDriver The user's avatar driver.
     * @return bool
     */
    public function updateConfiguration($perPage, $avatarDriver)
    {
        $this->loadUserDetails();

        $settings = [
            self::KEY_AVATAR_DRIVER => $avatarDriver,
            self::KEY_PER_PAGE => $perPage
        ];

        try {
            $settingContent = YAML::dump($settings);

            $results = file_put_contents($this->configurationPath, $settingContent);

            if ($results === false) {
                return false;
            }
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