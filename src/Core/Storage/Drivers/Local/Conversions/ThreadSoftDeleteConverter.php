<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Conversions;

use Stillat\Meerkat\Core\Storage\Drivers\Local\LocalThreadStorageManager;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;

/**
 * Class ThreadSoftDeleteConverter
 *
 * Provides utilities for updating legacy soft-deleted threads to use thread meta-data.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local\Conversions
 * @since 2.0.0
 */
class ThreadSoftDeleteConverter
{

    /**
     * Updates the soft deleted thread to use thread meta-data instead of directory names.
     *
     * @param LocalThreadStorageManager $manager A manager instance that can be used to update meta-data.
     * @param string $threadPath The thread storage path.
     * @param string $threadId The thread's string identifier.
     */
    public static function convert(LocalThreadStorageManager $manager, $threadPath, $threadId)
    {
        if (Str::startsWith($threadId, '_')) {
            $threadId = ltrim($threadId, '_');
        }

        $sourcePath = $threadPath . Paths::SYM_FORWARD_SEPARATOR . '_' . $threadId;
        $targetPath = $threadPath . Paths::SYM_FORWARD_SEPARATOR . $threadId;

        if (file_exists($sourcePath) == false || is_dir($sourcePath) == false) {
            return;
        }

        rename($sourcePath, $targetPath);

        $metaData = new ThreadMetaData();
        $metaData->setIsTrashed(true);

        $manager->updateMetaData($threadId, $metaData);
    }

}
