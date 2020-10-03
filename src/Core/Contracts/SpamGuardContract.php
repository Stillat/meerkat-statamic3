<?php

namespace Stillat\Meerkat\Core\Contracts;

use Stillat\Meerkat\Core\Guard\SpamReason;

/**
 * Interface SpamGuardContract
 *
 * Defines a consistent API for spam-guard implementations
 *
 * @package Stillat\Meerkat\Core\Contracts
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
     * @return boolean
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
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function getIsSpam(DataObjectContract $data);

    /**
     * Marks an object as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsSpam(DataObjectContract $data);

    /**
     * Marks a object as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsHam(DataObjectContract $data);

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingHam();

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingSpam();

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @return boolean
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
