<?php

namespace Stillat\Meerkat;

/**
 * Class PathProvider
 *
 * Provides utilities and helpers for interacting with the addon's storage directories.
 *
 * @package Stillat\Meerkat
 * @since 2.0.0
 */
class PathProvider
{

    const MEERKAT_COMMENTS_DIRECTORY = '/content/comments/';

    /**
     * Creates the Meerkat comments directory, if it does not exists.
     */
    public static function ensureContentPathExists()
    {
        $contentPath = PathProvider::contentPath();

        if (!file_exists($contentPath)) {
            mkdir($contentPath);
        }
    }

    /**
     * Resolves the absolute path to the Meerkat comments directory.
     *
     * @return string
     */
    public static function contentPath()
    {
        $configuredPath = config(Addon::CODE_ADDON_NAME . '.storage.path', null);

        if ($configuredPath !== null) {
            return $configuredPath;
        }

        return PathProvider::normalize(base_path(PathProvider::MEERKAT_COMMENTS_DIRECTORY));
    }

    public static function normalize($path)
    {
        return str_replace('\\', '/', $path);
    }

    public static function getRouteFile($file)
    {
        return PathProvider::normalize(realpath(PathProvider::getAddonDirectory() . 'routes/' . $file . '.php'));
    }

    /**
     * Gets the absolute path to the Meerkat addon directory.
     *
     * @param string $path An optional path suffix.
     * @return false|string
     */
    public static function getAddonDirectory($path = '')
    {
        return PathProvider::normalize(realpath(__DIR__ . './../' . $path));
    }

    public static function getResourcesDirectory($path = '')
    {
        return PathProvider::normalize(PathProvider::getAddonDirectory() . '/resources/' . $path);
    }

    public static function getStub($file)
    {
        return PathProvider::normalize(PathProvider::getAddonDirectory('src/_stubs/' . $file));
    }

    public static function winPath($path)
    {
        return str_replace('/', '\\', PathProvider::normalize($path));
    }

    public static function publicJsVendorPath($resourceName)
    {
        return url('vendor/' . Addon::CODE_ADDON_NAME . '/js/' . Addon::VERSION . '/' . $resourceName . '.js');
    }

}