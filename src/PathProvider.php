<?php

namespace Stillat\Meerkat;

/**
 * Class PathProvider
 *
 * Provides utilities and helpers for interacting with the addon's storage directories.
 *
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

        if (! file_exists($contentPath)) {
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
        $configuredPath = config(Addon::CODE_ADDON_NAME.'.storage.path', null);

        if ($configuredPath !== null) {
            return $configuredPath;
        }

        return PathProvider::normalize(base_path(PathProvider::MEERKAT_COMMENTS_DIRECTORY));
    }

    /**
     * Ensures that all directory separators are forward slashes.
     *
     * @param  string  $path The path to normalize.
     * @return string
     */
    public static function normalize($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Generates a path to the addon resource directory.
     *
     * @param  string  $path The relative path.
     * @return string
     */
    public static function getResourcesDirectory($path = '')
    {
        return PathProvider::normalize(PathProvider::getAddonDirectory().'/resources/'.$path);
    }

    /**
     * Gets the absolute path to the Meerkat addon directory.
     *
     * @param  string  $path An optional path suffix.
     * @return false|string
     */
    public static function getAddonDirectory($path = '')
    {
        return PathProvider::normalize(realpath(__DIR__.'/../'.$path));
    }

    /**
     * Generates a path to an addon stub file.
     *
     * @param  string  $file The relative path.
     * @return string
     */
    public static function getStub($file)
    {
        return PathProvider::normalize(PathProvider::getAddonDirectory('src/_stubs/'.$file));
    }

    /**
     * Ensures all directory separators are back slashes.
     *
     * @param  string  $path The path.
     * @return string
     */
    public static function winPath($path)
    {
        return str_replace('/', '\\', PathProvider::normalize($path));
    }

    /**
     * Generates a URL for the requested JavaScript resource name.
     *
     * @param  string  $resourceName The public resource name.
     * @return string
     */
    public static function publicJsVendorPath($resourceName)
    {
        return url('vendor/'.Addon::CODE_ADDON_NAME.'/js/'.Addon::VERSION.'/'.$resourceName.'.js');
    }
}
