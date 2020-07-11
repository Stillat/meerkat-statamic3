<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Stillat\Meerkat\Core\Helpers\Str;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;

/**
 * Class GTUBESpamGuard
 *
 * Generic Test for Unscolicted Bulk Email
 *
 * This spam guard implements the GTUBE test, which is a simple way
 * to verify that a spam filter setup is functioning properly.
 *
 * More information can be found here from Apache SpamAssassin:
 *    https://spamassassin.apache.org/gtube/
 *
 * @package Stillat\Meerkat\Core\Guard\Providers
 * @since 2.0.0
 */
class GTUBESpamGuard implements SpamGuardContract
{

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
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess()
    {
        return true;
    }

    /**
     * Returns a value indicating if the provided object has a
     * high probability of being a disingenuous posting.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function getIsSpam(DataObjectContract $data)
    {
        foreach ($data->getDataAttributes() as $item) {
            if (is_string($item)) {
                if (Str::contains($item, 'XJS*C4JDBQADN1.NSBN3*2IDNEN*GTUBE-STANDARD-ANTI-UBE-TEST-EMAIL*C.34X')) {
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
     * @param  DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsSpam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Marks a object as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param  DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsHam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingHam()
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingSpam()
    {
        return false;
    }

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @since 2.0.0
     * @return boolean
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
