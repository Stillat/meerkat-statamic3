<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Class UtilitiesController
 *
 * @since 2.2.3
 */
class UtilitiesController extends CpController
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files = null;

    public function __construct(Request $request, Filesystem $files)
    {
        parent::__construct($request);

        $this->files = $files;
    }

    public function clearSiteRoutesCache()
    {
        $cachedRoutesPath = app()->getCachedRoutesPath();

        if ($this->files->exists($cachedRoutesPath)) {
            $this->files->delete($cachedRoutesPath);
        }

        return back()->withSuccess(trans('meerkat::validation.route_cache_cleared'));
    }
}
