<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatically Detect Spatie Ray
    |--------------------------------------------------------------------------
    |
    | When enabled, a Spatie Ray-compatible error reporter
    | will be registered automatically when installed.
    |
    */
    'auto_discover_spatie_ray' => true,

    /*
    |--------------------------------------------------------------------------
    | Error Reporters
    |--------------------------------------------------------------------------
    |
    | A list of all error reporters to assist with development.
    |
    */
    'reporters' => [
        \Stillat\Meerkat\Core\Logging\Reporters\ExceptionReporter::class,
    ]

];
