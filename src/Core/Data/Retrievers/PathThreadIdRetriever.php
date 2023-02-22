<?php

namespace Stillat\Meerkat\Core\Data\Retrievers;

use Stillat\Meerkat\Core\Storage\Paths;

/**
 * Class PathThreadIdRetriever
 *
 * Provides utilities for retrieving a thread identifier from a storage path.
 *
 * @since 2.0.0
 */
class PathThreadIdRetriever
{
    /**
     * Attempts to retrieve a thread identifier from a comment's storage path.
     *
     * @param  string  $storagePath The comment storage path.
     * @return string|null
     */
    public static function idFromStoragePath($storagePath)
    {
        $sharedPathsInstance = Paths::makeNew();

        if ($sharedPathsInstance !== null) {
            $relativePath = $sharedPathsInstance->makeRelative($storagePath);

            if (mb_strlen(trim($relativePath)) === 0) {
                return null;
            }

            $parts = explode(Paths::SYM_FORWARD_SEPARATOR, $relativePath);

            if (count($parts) === 0) {
                return null;
            }

            return $parts[0];
        }

        return null;
    }
}
