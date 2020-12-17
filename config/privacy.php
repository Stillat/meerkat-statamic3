<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store User Agent Information
    |--------------------------------------------------------------------------
    |
    | Controls whether visitor user agent information is stored when they
    | submit a comment. A user agent contains information about their
    | browser, operating system, and potentially other information.
    |
    | Some spam guards may utilize this to improve spam detection.
    |
    */
    'store_user_agent' => true,

    /*
    |--------------------------------------------------------------------------
    | Store User IP Address Information
    |--------------------------------------------------------------------------
    |
    | Controls whether visitor user IP address information is stored when
    | they submit a comment. A user's IP address could be used to find
    | a rough approximation of real-world geo-location or network.
    |
    | Some spam guards may utilize this to improve spam detection.
    |
    */
    'store_user_ip' => true,

    /*
    |--------------------------------------------------------------------------
    | Store User HTTP Referrer Header
    |--------------------------------------------------------------------------
    |
    | Controls whether visitor HTTP Referrer header information is stored when
    | they submit a comment. The Referrer header may be used to reconstruct
    | user behavior/activity trends based on the pages they visit online.
    |
    | Some spam guards may utilize this to improve spam detection.
    |
    */
    'store_referrer' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Anonymous User Email Address
    |--------------------------------------------------------------------------
    |
    | The following will be used for internal processes that require
    | email addresses when a visitor has not left an email address.
    |
    */
    'anonymous_email' => 'no-email@example.org',

    /*
    |--------------------------------------------------------------------------
    | Default Anonymous User Name
    |--------------------------------------------------------------------------
    |
    | The following will be used in place of a name when users
    | are not required to enter names, and have not done so.
    |
    */
    'anonymous_author' => 'Anonymous User',

];
