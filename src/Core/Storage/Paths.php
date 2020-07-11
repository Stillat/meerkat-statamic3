<?php

namespace Stillat\Meerkat\Core\Storage;

use Stillat\Meerkat\Core\Assertions\TypeAssertions;

/**
 * Provides cross-platform path-related utility methods
 * 
 * @since 2.0.0
 */
class Paths
{
    const SYM_FORWARD_SEPARATOR = '/';

    /**
     * The Meerkat configuration instance.
     *
     * @var \Stillat\Meerkat\Core\Configuration
     */
    private $config = null;

    /**
     * A cleaned up version of the configured storage root directory.
     *
     * @var string
     */
    private $cleanedStorageRoot = '';

    /**
     * Constructs a new instance of Paths.
     *
     * @param \Stillat\Meerkat\Core\Configuration $config
     */
    public function __construct($config)
    {
        TypeAssertions::assertIsMeerkatConfiguration($config, '$config');

        $this->config = $config;

        // Create the cleaned storage root path.
        $this->cleanedStorageRoot = $this->cleanSegment($this->config->storageDirectory);
    }

    /**
     * Removes all leading/trailing back and forward slashes.
     *
     * @param  string $segment
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
     * @param  string[] $segments The path segments to combine.
     *
     * @return string
     */
    public function combine()
    {
        if (func_num_args() == 0) {
            return null;
        }

        $segments = func_get_args();

        array_walk($segments, [$this, 'cleanSegment']);

        return $this->cleanSegment(join($this->config->directorySeparator, $segments));
    }

    /**
     * Combines the provided path segments with the root storage path and returns it.
     *
     * @param string[] $segments The path segments to combine.
     *
     * @return string
     */
    public function combineWithStorage()
    {
        if (func_num_args() == 0) {
            return null;
        }

        $segments = func_get_args();

        array_walk($segments, [$this, 'cleanSegment']);

        // We've already cleaned the root storage directory; just put it at the beginning.
        array_unshift($segments, $this->cleanedStorageRoot);

        return $this->cleanSegment(join($this->config->directorySeparator, $segments));
    }

    /**
     * Converts the path to a path relative to the storage root.
     *
     * @param  string $path The path to convert.
     * @return string|boolean
     */
    public function makeRelative($path)
    {
        $rootPath = $this->combineWithStorage('');

        if (mb_strlen($rootPath) > mb_strlen($path)) {
            return $this->cleanSegment($path);
        }

        return $this->cleanSegment(mb_substr($path, mb_strlen(($rootPath))));
    }
}
