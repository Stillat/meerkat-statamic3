<?php

namespace Stillat\Meerkat\Core;

/**
 * Class GuardConfiguration
 *
 * Contains guard/spam-service related configuration items
 *
 * @since 2.0.0
 */
class GuardConfiguration extends ConfigurationContainer
{
    /**
     * Indicates if spam results should be automatically
     * submitted to third-party providers, if available.
     *
     * @var bool
     */
    public $autoSubmitSpamToThirdParties = false;

    /**
     * Indicates if the spam service should continue to check
     * all spam services even if one has identified an item.
     *
     * @var bool
     */
    public $checkAgainstAllGuardServices = false;

    /**
     * Indicates if comments should automatically be marked
     * as "un-published" if an error occurs when checking
     * with a spam guard service. Default is true.
     *
     * @var bool
     */
    public $unpublishOnGuardFailures = true;

    /**
     * A list of banned words that comments should not contain.
     *
     * @var array
     */
    public $bannedWords = [];

    /**
     * A list of IP Addresses to reject comments from.
     *
     * @var array
     */
    public $blockedIpAddresses = [];

    /**
     * Indicates if comments should be automatically deleted if they are detected to be spam, or marked as spam.
     *
     * @var bool
     */
    public $autoDeleteSpam = false;
}
