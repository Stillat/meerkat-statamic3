<?php

namespace Stillat\Meerkat\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class RequestHelpers
 *
 * Provides HTTP related helper methods.
 *
 * @package Stillat\Meerkat\Http
 * @since 2.0.0
 */
class RequestHelpers
{

    /**
     * Determines if the current request is accessing the Statamic Control Panel.
     *
     * @param $request Request The request context.
     * @return bool
     */
    public static function isControlPanelRequest($request)
    {
        if ($request == null) {
            return false;
        }

        $statamicCpRoute = config('statamic.cp.route');

        if ($statamicCpRoute == null || is_string($statamicCpRoute) == false || mb_strlen(trim($statamicCpRoute)) == 0) {
            return false;
        }

        $statamicCpRoute = trim($statamicCpRoute, '\//');
        $requestPath = trim($request->path(), '\//');


        if (Str::startsWith($requestPath, $statamicCpRoute)) {
            return true;
        }

        return false;
    }

    public static function isControlPanelRequestFromHeaders($request)
    {
        $referrer = strtolower($request->headers->get('referer'));
        $appUrl = strtolower(env('APP_URL'));

        if (mb_strlen($appUrl) > mb_strlen($referrer)) {
            return false;
        }

        if (Str::startsWith($referrer, $appUrl)) {
            $referrer = mb_substr($referrer, mb_strlen($appUrl));
            $statamicCpRoute = config('statamic.cp.route');
            $statamicCpRoute = trim($statamicCpRoute, '\//');


            if (Str::startsWith($referrer, '/' . $statamicCpRoute . '/')) {

                return true;
            }
        }


        return false;
    }

}
