<?php

namespace Stillat\Meerkat\Core;

/**
 * Class DataPrivacyConfiguration
 *
 * The main Meerkat configuration entry point
 *
 * The Configuration class defines and manages various
 * configuration options that change the behavior
 * of the Meerkat Core processes and actions.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.1.14
 */
class DataPrivacyConfiguration extends ConfigurationContainer
{

    /**
     * Indicates if Meerkat will automatically collect visitor's user agents when they submit a comment.
     *
     * @var bool
     */
    public $collectUserAgent = false;

    /**
     * Indicates if Meerkat will automatically collect visitor's IP Addresses when they submit a comment.
     *
     * @var bool
     */
    public $collectUserIp = false;

    /**
     * Indicates if Meerkat will automatically collect visitor's HTTP Referrer header when they submit a comment.
     * @var bool
     */
    public $collectReferrer = false;

    /**
     * The email address to use for internal processes when a visitor has not provided an email address.
     *
     * @var string
     */
    public $emptyEmailAddress = 'no-email@example.org';

    /**
     * The display name to use when a visitor has not provided a name.
     *
     * @var string
     */
    public $emptyName = 'Anonymous User';

}
