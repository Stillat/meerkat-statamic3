<?php

namespace Stillat\Meerkat\Core\Contracts;

use Stillat\Meerkat\Core\Guard\SpamReason;

/**
 * Interface SpamGuardContract
 *
 * Defines a consistent API for spam-guard implementations
 *
 * @since 2.0.0
 */
interface SpamGuardContract
{
    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return bool
     */
    public function wasSuccess();

    /**
     * Gets the reasons the item was marked as spam.
     *
     * @return SpamReason[]
     */
    public function getSpamReasons();

    /**
     * Returns a value indicating if the provided object has a
     * high probability of being a disingenuous posting.
     *
     *
     * @return bool
     */
    public function getIsSpam(DataObjectContract $data);

    /**
     * Marks an object as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsSpam(DataObjectContract $data);

    /**
     * Marks a object as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsHam(DataObjectContract $data);

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingHam();

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingSpam();

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function hasErrors();

    /**
     * Returns a collection of errors, if available.
     *
     * @return array
     */
    public function getErrors();
}
