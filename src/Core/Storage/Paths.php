<?php

namespace Stillat\Meerkat\Core\Storage;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Stillat\Meerkat\Core\Configuration;

/**
 * Class Paths
 *
 * Provides cross-platform path-related utility methods
 *
 * @package Stillat\Meerkat\Core\Storage
 * @since 2.0.0
 */
class Paths
{

    /**
     * The default directory permissions to use.
     */
    const DIRECTORY_PERMISSIONS = 644;

    /**
     * The directory separator that is used internally.
     */
    const SYM_FORWARD_SEPARATOR = '/';

    /**
     * The Meerkat configuration instance.
     *
     * @var Configuration
     */
    private $config = null;

    /**
     * A cleaned up version of the configured storage root directory.
     *
     * @var string
     */
    private $cleanedStorageRoot = '';

    public function __construct($config)
    {
        $this->config = $config;

        // Create the cleaned storage root path.
        $this->cleanedStorageRoot = $this->cleanSegment($this->config->storageDirectory);
    }

    /**
     * Removes all leading/trailing back and forward slashes.
     *
     * @param string $segment
     *
     * @return string
     */
    private function cleanSegment($segment)
    {
        $segment = trim($segment, '/');
        $segment = trim($segment, '\\');

        return $segment;
    }

    /**
     * Combines the provided path segments and returns the created path.
     *
     * @param string[] $segments The path segments to combine.
     *
     * @return string
     */
    public function combine(array $segments)
    {
        array_walk($segments, [$this, 'cleanSegment']);

        return $this->normalize($this->cleanSegment(join($this->config->directorySeparator, $segments)));
    }

    /**
     * Normalizes the directory separators in the provided path.
     *
     * @param string $path The path to normalize.
     * @return string|string[]
     */
    public function normalize($path)
    {
        return str_replace('\\', self::SYM_FORWARD_SEPARATOR, $path);
    }

    /**
     * Converts the path to a path relative to the storage root.
     *
     * @param string $path The path to convert.
     * @return string|boolean
     */
    public function makeRelative($path)
    {
        $rootPath = $this->combineWithStorage([]);

        if (mb_strlen($rootPath) > mb_strlen($path)) {
            return $this->cleanSegment($path);
        }

        return $this->cleanSegment(mb_substr($path, mb_strlen(($rootPath))));
    }

    /**
     * Combines the provided path segments with the root storage path and returns it.
     *
     * @param string[] $segments The path segments to combine.
     *
     * @return string
     */
    public function combineWithStorage(array $segments)
    {
        array_walk($segments, [$this, 'cleanSegment']);

        // We've already cleaned the root storage directory; just put it at the beginning.
        array_unshift($segments, $this->cleanedStorageRoot);

        return $this->cleanSegment(join($this->config->directorySeparator, $segments));
    }

    public function seekFiles($directory, $targetFile)
    {
        $result = [];
        $dir = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($dir);

        foreach ($dir as $info) {
            $result[] = $info->getPathname();
        }


        return $result;
    }

    public function getFilesRecursively($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->getFilesRecursively($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
