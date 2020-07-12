<?php

namespace Stillat\Meerkat\Core;

/**
 * Class Errors
 *
 * Provides a consolidated location for Meerkat Core error codes.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
class Errors
{

    /**
     * The web server does not have sufficient privileges to write
     * and/or create the configured Meerkat storage directory.
     */
    const DRIVER_LOCAL_INSUFFICIENT_PRIVILEGES = '01-001';
    const DRIVER_THREAD_NONE_SUPPLIED = '01-002';
    const DRIVER_COMMENT_NONE_SUPPLIED = '01-003';
    const DRIVER_THREAD_PROVIDED_NOT_FOUND = '01-004';
    const DRIVER_COMMENT_PROVIDED_NOT_FOUND = '01-005';
    const DRIVER_CONFIGURATION_NOT_FOUND = '01-006';
    const DRIVER_THREAD_CANNOT_USE = '01-007';
    const DRIVER_COMMENT_CANNOT_USE = '01-008';

    const HTTP_CLIENT_REQUEST_FAILED = '01-009';

    const HOST_TEMPLATE_SYSTEM_FAILURE = '01-009';

    const GUARD_GENERAL_API_REQUEST_FAILURE = '02-007';
    const GUARD_AKISMET_RESPONSE_FAILURE = '02-002';
    const GUARD_AKISMET_MISSING_API_KEY = '02-003';
    const GUARD_AKISMET_MISSING_HOME_URL = '02-004';


}