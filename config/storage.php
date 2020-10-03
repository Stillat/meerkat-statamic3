<?php

return [

    'path' => base_path('content/comments'),

    'trackChanges' => true,

    'drivers' => [
        'comments' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager::class,
        'spam_reports' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalGuardReportStorageManager::class,
        'threads' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager::class,
    ]

];
