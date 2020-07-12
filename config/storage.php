<?php

return [

    'path' => base_path('content/comments'),

    'drivers' => [
        'comments' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalCommentStorageManager::class,
        'threads' => \Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager::class,
    ]

];
