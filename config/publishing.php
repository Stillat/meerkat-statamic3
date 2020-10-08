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
        \Stillat\Meerkat\Core\Guard\Providers\GTUBESpamGuard::class,
        \Stillat\Meerkat\Core\Guard\Providers\WordFilterSpamGuard::class
    ],

    'auto_publish' => false,
    'auto_publish_authenticated_users' => true,

    'automatically_close_comments' => '0',
    'guard_check_all_providers' => false,
    'guard_unpublish_on_guard_failure' => false,
    'auto_check_spam' => true,
    'auto_delete_spam' => false,
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
