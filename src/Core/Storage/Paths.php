<?php

namespace Stillat\Meerkat\Core\Storage;

use DirectoryIterator;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\ConfigurationFactories;
use Stillat\Meerkat\Core\Support\Env;
use Stillat\Meerkat\Core\Support\Str;

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
    public static $directoryPermissions = 0755;

    /**
     * The directory separator that is used internally.
     */
    const SYM_FORWARD_SEPARATOR = '/';

    /**
     * A cached instance of a shared Paths instance.
     *
     * @var null|Paths
     */
    protected static $cachedSharedInstance = null;

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
     * Attempts to create a new Paths instance from shared configuration.
     *
     * @return Paths|null
     */
    public static function makeNew()
    {
        if (ConfigurationFactories::hasConfigurationInstance()) {
            if (self::$cachedSharedInstance === null) {
                self::$cachedSharedInstance = new Paths(ConfigurationFactories::$configurationInstance);
            }

            return self::$cachedSharedInstance;
        }

        return null;
    }

    /**
     * Gets a directory listing for the provided path. Not recursive.
     *
     * @param string $path The directory to search.
     * @return string[]
     */
    public static function getDirectories($path)
    {
        if (file_exists($path) === false || is_dir($path) === false) {
            return [];
        }

        $dirIterator = new DirectoryIterator($path);
        $pathsToReturn = [];

        foreach ($dirIterator as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isFile()) {
                continue;
            }

            $pathsToReturn[] = $fileInfo->getRealPath();
        }

        return $pathsToReturn;
    }

    /**
     * Attempts to copy all the source directories contents to the destination directory.
     *
     * @param string $source The path that things should be copied from.
     * @param string $destination The path that things should be copied to.
     * @param bool $cleanUpSource Whether to remove all the contents from the source directory.
     */
    public static function recursivelyCopyDirectory($source, $destination, $cleanUpSource)
    {
        if (file_exists($destination) == false) {
            mkdir($destination, Paths::$directoryPermissions, true);
        }

        if (is_dir($source)) {
            $dirHandle = opendir($source);

            while (false !== ($file = readdir($dirHandle))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source . Paths::SYM_FORWARD_SEPARATOR . $file)) {
                        Paths::recursivelyCopyDirectory(
                            $source . Paths::SYM_FORWARD_SEPARATOR . $file,
                            $destination . Paths::SYM_FORWARD_SEPARATOR . $file,
                            false
                        );
                    } else {
                        copy(
                            $source . Paths::SYM_FORWARD_SEPARATOR . $file,
                            $destination . Paths::SYM_FORWARD_SEPARATOR . $file
                        );
                    }
                }
            }

            closedir($dirHandle);
        }

        if ($cleanUpSource) {
            Paths::recursivelyRemoveDirectory($source);
        }
    }

    /**
     * Recursively removes the contents of a directory.
     *
     * @param string $directory The path to remove.
     */
    public static function recursivelyRemoveDirectory($directory)
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($directory . Paths::SYM_FORWARD_SEPARATOR . $object) == 'dir') {
                        Paths::recursivelyRemoveDirectory($directory . Paths::SYM_FORWARD_SEPARATOR . $object);
                    } else {
                        unlink($directory . Paths::SYM_FORWARD_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($directory);
        }
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

        return $this->normalize($this->cleanSegment(mb_substr($path, mb_strlen(($rootPath)))));
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

        return $this->normalize($this->cleanSegment(join($this->config->directorySeparator, $segments)));
    }

    /**
     * Gets all the files with the given pattern, recursively.
     *
     * @param string $pattern The glob search pattern.
     * @param int $flags The glob flags.
     * @return array|false
     */
    public function getFilesRecursively($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->getFilesRecursively($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

    /**
     * Recursively searched for a file using multiple patterns.
     *
     * @param string $pattern The primary pattern.
     * @param string $subPattern The sub-pattern to search.
     * @param string $fileName The name of the file to locate.
     * @return array|false|string|string[]
     */
    public function searchForFile($pattern, $subPattern, $fileName)
    {
        $files = glob($pattern, 0);

        if (Str::startsWith($pattern, Paths::SYM_FORWARD_SEPARATOR) === false) {
            $pattern = Paths::SYM_FORWARD_SEPARATOR . $pattern;
        }

        foreach ($files as $file) {
            $target = $this->combine([$file, $fileName]);

            if (Str::endsWith($target, $subPattern)) {
                if (file_exists($target)) {
                    return $target;
                }
            }
        }

        foreach (glob(dirname($pattern) . '/*', GLOB_NOSORT) as $dir) {
            $temp = $this->searchForFile($dir . '/' . basename($pattern), $subPattern, $fileName);

            if (is_array($temp) && count($temp) > 0) {
                $files = array_merge($files, $temp);
            } else if (is_string($temp)) {
                return $temp;
            }
        }

        return $files;
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
        $path = str_replace('\\', self::SYM_FORWARD_SEPARATOR, $path);

        if (Env::isWindows() === false && Str::startsWith($path, Paths::SYM_FORWARD_SEPARATOR) === false) {
            $path = Paths::SYM_FORWARD_SEPARATOR . $path;
        }

        return $path;
    }

}
