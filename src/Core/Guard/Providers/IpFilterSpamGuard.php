<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Guard\SpamReason;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class IpFilterSpamGuard
 *
 * Determines if a comment is spam by checking its IP Address against a list of blocked IP Addressses.
 *
 * @since 2.1.0
 */
class IpFilterSpamGuard implements SpamGuardContract
{
    const IPF_MATCHED = 'IPF-01-001';

    const IPF_DEFAULT_MESSAGE = 'IP filter matched against a configured value.';

    private $reasons = [];

    /**
     * The Meerkat Guard configuration instance.
     *
     * @var GuardConfiguration
     */
    private $guardConfig = null;

    public function __construct(GuardConfiguration $config)
    {
        $this->guardConfig = $config;
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public static function getConfigName()
    {
        return 'IP Address Filter';
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'IpFilter';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return bool
     */
    public function wasSuccess()
    {
        return true;
    }

    /**
     * Gets the reasons the item was marked as spam.
     *
     * @return SpamReason[]
     */
    public function getSpamReasons()
    {
        return $this->reasons;
    }

    /**
     * Returns a value indicating if the provided object has a
     * high probability of being a disingenuous posting.
     *
     *
     * @return bool
     */
    public function getIsSpam(DataObjectContract $data)
    {
        if (count($this->guardConfig->blockedIpAddresses) === 0) {
            return false;
        }

        $ipAddress = mb_strtolower(trim($data->getDataAttribute(AuthorContract::KEY_USER_IP)));

        foreach ($this->guardConfig->blockedIpAddresses as $address) {
            $checkAddress = mb_strtolower(trim($address));

            if ($checkAddress === $ipAddress) {
                $reason = new SpamReason();
                $reason->setReasonText(self::IPF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::IPF_MATCHED);
                $reason->setReasonContext([
                    'address' => $address,
                    'property' => AuthorContract::KEY_USER_IP,
                ]);

                $this->reasons[] = $reason;

                return true;
            }
        }

        return false;
    }

    /**
     * Marks an object as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsSpam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Marks a object as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsHam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingHam()
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingSpam()
    {
        return false;
    }

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function hasErrors()
    {
        return false;
    }

    /**
     * Returns a collection of errors, if available.
     *
     * @return array
     */
    public function getErrors()
    {
        return [];
    }
}
