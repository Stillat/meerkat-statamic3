<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Local Storage Path
    |--------------------------------------------------------------------------
    |
    | The filesystem directory where comments and threads will be saved.
    |
    */
    'path' => base_path('content/comments'),

    /*
    |--------------------------------------------------------------------------
    | Comment Revision Support
    |--------------------------------------------------------------------------
    |
    | This value controls whether  changes to comments should be tracked
    | and stored. Enabling this allows for changes to be reverted.
    |
    */
    'track_changes' => true,

    /*
    |--------------------------------------------------------------------------
    | Storage Drivers
    |--------------------------------------------------------------------------
    |
    | These values control which storage drivers will be used by different sub-systems.
    |
    */
    'drivers' => [
        'comments' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager::class,
        'spam_reports' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalGuardReportStorageManager::class,
        'threads' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager::class,
        'tasks' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalTaskStorageManager::class,
    ],

];
