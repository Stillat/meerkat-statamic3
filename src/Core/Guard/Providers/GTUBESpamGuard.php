<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Guard\SpamReason;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class GTUBESpamGuard
 *
 * Generic Test for unsolicited Bulk Email
 *
 * This spam guard implements the GTUBE test, which is a simple way
 * to verify that a spam filter setup is functioning properly.
 *
 * More information can be found here from Apache SpamAssassin:
 *    https://spamassassin.apache.org/gtube/
 *
 * @since 2.0.0
 */
class GTUBESpamGuard implements SpamGuardContract
{
    const GTUBE_MATCH_REASON = 'GTSG-01-001';

    const GTUBE_MATCH_DEFAULT_MESSAGE = 'Message contained GTUBE test string.';

    const GTUBE_TEST_STRING = 'XJS*C4JDBQADN1.NSBN3*2IDNEN*GTUBE-STANDARD-ANTI-UBE-TEST-EMAIL*C.34X';

    /**
     * The reasons the item was marked as spam.
     *
     * @var SpamReason[]
     */
    protected $reasons = [];

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public static function getConfigName()
    {
        return 'GTUBE Detector';
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'GTUBEDetector';
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
     * Gets a value indicating if the detector succeeded.
     *
     * @return bool
     */
    public function wasSuccess()
    {
        return true;
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
        foreach ($data->getDataAttributes() as $item) {
            if (is_string($item)) {
                if (Str::contains($item, self::GTUBE_TEST_STRING)) {
                    $reason = new SpamReason();
                    $reason->setReasonCode(self::GTUBE_MATCH_REASON);
                    $reason->setReasonText(self::GTUBE_MATCH_DEFAULT_MESSAGE);
                    $reason->setReasonContext([
                        'position' => mb_strpos($item, self::GTUBE_TEST_STRING),
                    ]);

                    $this->reasons[] = $reason;

                    return true;
                }
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
