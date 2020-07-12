<?php

namespace Stillat\Meerkat;


use Stillat\Meerkat\Support\Facades\Configuration;

class PathProvider
{

    const MEERKAT_COMMENTS_DIRECTORY = '/content/comments/';

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

        return base_path(PathProvider::MEERKAT_COMMENTS_DIRECTORY);
    }

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
     * Gets the absolute path to the Meerkat addon directory.
     *
     * @param string $path An optional path suffix.
     * @return false|string
     */
    public static function getAddonDirectory($path = '')
    {
        return realpath(__DIR__.'./../'.$path);
    }

    public static function getRouteFile($file)
    {
        return realpath(PathProvider::getAddonDirectory().'routes/'.$file.'.php');
    }

    public static function getResourcesDirectory($path = '')
    {
        return PathProvider::getAddonDirectory().'/resources/'.$path;
    }

    public static function getStub($file)
    {
        return PathProvider::getAddonDirectory('src/_stubs/'.$file);
    }

}