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
    | Storage Permissions
    |--------------------------------------------------------------------------
    |
    | The following values control the permissions that will be used when
    | directories and files are automatically created. The permissions
    | specified as the defaults assume storage is below the web root.
    |
    */
    'permissions' => [
        'directory' => 0755,
        'file'      => 644
    ],

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
        'mail' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalEmailReportStorageManager::class,
        'user_config' => \Stillat\Meerkat\Configuration\Drivers\Local\LocalUserSettingsConfigurationStorageManager::class,
        'supplemental_config' => \Stillat\Meerkat\Configuration\Drivers\Local\LocalSupplementalSettingsStorageManager::class,
    ],

];
