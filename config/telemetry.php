<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Telemetry Submissions
    |--------------------------------------------------------------------------
    |
    | This value controls whether or not Meerkat is allowed to send
    | data to backend services to help identify issues, fix bugs
    | and improve the product's experience and reliability.
    |
    | Meerkat does not submit data without human interaction.
    |
    */
    'send' => true,

    /*
    |--------------------------------------------------------------------------
    | Telemetry Fields
    |--------------------------------------------------------------------------
    |
    | This configuration mapping allows you to customize what data is sent.
    |
    */
    'errors' => [
        // Indicates if the addon name, vendor, and version of all addons
        // will be submitted with error logs or product crash reports.
        'submit_addon_data' => true
    ],

];
