<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spam Guards
    |--------------------------------------------------------------------------
    |
    | This value controls which spam guards are loaded when Meerkat starts.
    |
    */
    'guards' => [
        \Stillat\Meerkat\Core\Guard\Providers\AkismetSpamGuard::class,
        \Stillat\Meerkat\Core\Guard\Providers\WordFilterSpamGuard::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Publishing
    |--------------------------------------------------------------------------
    |
    | This value controls whether comments are automatically made
    | live to all your site's visitors. It is recommended to
    | leave this set to false so comments can be reviewed.
    |
    */
    'auto_publish' => false,

    /*
    |--------------------------------------------------------------------------
    | Automatic Authenticated User Publishing
    |--------------------------------------------------------------------------
    |
    | This value controls whether comments are automatically made
    | live to all your site's visitors if left by a currently
    | authenticated site user. The comment submissions's
    | email must match the current user's email value.
    |
    */
    'auto_publish_authenticated_users' => true,


    /*
    |--------------------------------------------------------------------------
    | Automatic Thread Closing
    |--------------------------------------------------------------------------
    |
    | This value controls the number of days comments will be accepted
    | after a post, page, or entry has been published to the site.
    |
    | Setting a value of 0 disables this feature.
    |
    */
    'automatically_close_comments' => 0,

    /*
    |--------------------------------------------------------------------------
    | Utilize All Spam Guards
    |--------------------------------------------------------------------------
    |
    | This value controls whether all spam guards will be utilized for
    | each spam check. By default, if a comment is detected to be
    | spam, it will stop checking against other spam detectors.
    |
    */
    'guard_check_all_providers' => false,

    /*
    |--------------------------------------------------------------------------
    | Unpublish Comments After Guard Failures
    |--------------------------------------------------------------------------
    |
    | This value controls whether comments will be automatically
    | unpublished if errors occur while checking it for spam.
    |
    */
    'guard_unpublish_on_guard_failure' => false,

    /*
    |--------------------------------------------------------------------------
    | Automatically Check All New Submissions
    |--------------------------------------------------------------------------
    |
    | This value controls whether comments will be automatically
    | checked for spam when they are submitted to the site.
    |
    */
    'auto_check_spam' => true,

    /*
    |--------------------------------------------------------------------------
    | Automatically Remove Spam
    |--------------------------------------------------------------------------
    |
    | This value controls whether comments will be automatically
    | removed if they are identified as spam by any provider.
    |
    */
    'auto_delete_spam' => false,

    /*
    |--------------------------------------------------------------------------
    | Automatically Submit Moderator Results
    |--------------------------------------------------------------------------
    |
    | This value controls whether false negative/positive spam results
    | will be sent to third-party providers who support this feature.
    |
    */
    'auto_submit_results' => false,

    /*
    |--------------------------------------------------------------------------
    | Honeypot Blueprint Field
    |--------------------------------------------------------------------------
    |
    | If Meerkat detects the configured field has a value when a comment
    | is submitted to your site, the submission will be silently
    | ignored; use this to help prevent automated spamming.
    |
    | The key is the Meerkat configuration item, the value is the field name.
    |
    */
    'honeypot' => 'honeypot',

];
