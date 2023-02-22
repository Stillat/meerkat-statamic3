<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Send Automatic Emails
    |--------------------------------------------------------------------------
    |
    | When enabled, automatic emails will be sent when new
    | comments are submitted to your site. Meerkat will
    | use the existing Statamic email configuration.
    |
    | See: https://statamic.dev/email
    |
    */
    'send_mail' => false,

    /*
    |--------------------------------------------------------------------------
    | From Address
    |--------------------------------------------------------------------------
    |
    | By default the "from" address will be the email of the
    | comment author when sending automated emails. Change
    | this value from "null" to a different value to use
    | a different "from" email address when sending.
    |
    */
    'from_address' => null,

    /*
    |--------------------------------------------------------------------------
    | Check Comment Against Spam Guard
    |--------------------------------------------------------------------------
    |
    | When enabled, only comments not labeled as
    | spam will trigger an automated email.
    |
    */
    'check_with_spam_guard' => true,

    /*
    |--------------------------------------------------------------------------
    | Show Control Panel Button
    |--------------------------------------------------------------------------
    |
    | If enabled, a "View in Control Panel" button will be added to emails.
    |
    */
    'show_control_panel_button' => true,

    /*
    |--------------------------------------------------------------------------
    | Email Addresses
    |--------------------------------------------------------------------------
    |
    | A list of email addresses to send automated submission emails to.
    |
    */
    'addresses' => [

    ],

];
