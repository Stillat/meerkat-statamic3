<?php

namespace Stillat\Meerkat\Http\Emitter;

use Illuminate\Support\Facades\Route;
use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Http\RequestHelpers;

/**
 * Class Emit
 *
 * Provides utilities for emitting dynamic stylesheets and JavaScript assets.
 *
 * @since 2.0.0
 */
class Emit
{
    /**
     * A list of all dynamic asset emitters registered at run-time.
     *
     * @var array
     */
    public static $registeredEmitters = [];

    /**
     * Determines if a dynamic asset has been registered.
     *
     * @param  string  $emissionName The dynamic asset to check for.
     * @return bool
     */
    public static function contains($emissionName)
    {
        return in_array($emissionName, self::$registeredEmitters);
    }

    /**
     * Injects a dynamic stylesheet asset into the Control Panel request.
     *
     * @param  string  $dynamicCssName The CSS asset name.
     * @param  callable  $callback The stylesheet generation callback.
     */
    public static function cpCss($dynamicCssName, $callback)
    {
        if (RequestHelpers::isControlPanelRequestFromHeaders(request())) {
            self::$registeredEmitters[] = $dynamicCssName;

            Emit::css($dynamicCssName, $callback);
        }
    }

    /**
     * Injects a dynamic stylesheet into a general Web request.
     *
     * @param  string  $dynamicCssName The CSS asset name.
     * @param  callable  $callback The stylesheet generation callback.
     */
    public static function css($dynamicCssName, $callback)
    {
        self::$registeredEmitters[] = $dynamicCssName;

        $assetNameForStatamic = './../'.Addon::CODE_ADDON_NAME;
        $fileName = basename($dynamicCssName);

        Statamic::style($assetNameForStatamic, $fileName);

        Route::get('/'.Addon::CODE_ADDON_NAME.'/css/'.$fileName.'.css', function () use ($callback) {
            $content = $callback();

            return response($content)->header('Content-Type', 'text/css');
        });
    }

    /**
     * Injects a dynamic JavaScript asset into the Control Panel request.
     *
     * @param  string  $dynamicJsName The JavaScript asset name.
     * @param  callable  $callback The JavaScript generation callback.
     */
    public static function cpJs($dynamicJsName, $callback)
    {
        if (RequestHelpers::isControlPanelRequestFromHeaders(request())) {
            self::$registeredEmitters[] = $dynamicJsName;

            Emit::js($dynamicJsName, $callback);
        }
    }

    /**
     * Injects a dynamic JavaScript asset into a general Web request.
     *
     * @param  string  $dynamicJsName The JavaScript asset name.
     * @param  callable  $callback The JavaScript generation callback.
     */
    public static function js($dynamicJsName, $callback)
    {
        self::$registeredEmitters[] = $dynamicJsName;

        $assetNameForStatamic = './../'.Addon::CODE_ADDON_NAME;
        $fileName = basename($dynamicJsName);

        Statamic::script($assetNameForStatamic, $fileName);

        Route::get('/'.Addon::CODE_ADDON_NAME.'/js/'.$fileName.'.js', function () use ($callback) {
            $content = $callback();

            return response($content)->header('Content-Type', 'application/javascript');
        });
    }
}
