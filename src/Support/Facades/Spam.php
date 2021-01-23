<?php

namespace Stillat\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Meerkat\Core\Guard\SpamServiceWrapper;
use Stillat\Meerkat\Core\Guard\Specimen;
use Stillat\Meerkat\Core\Guard\GuardResult;

/**
 * Class Spam
 * @package Stillat\Meerkat\Support\Facades
 *
 * @method static bool isSpam(Specimen $specimen)
 * @method static GuardResult submitSpam(Specimen $specimen)
 * @method static GuardResult submitHam(Specimen $specimen)
 */
class Spam extends Facade
{

    protected static function getFacadeAccessor()
    {
        return SpamServiceWrapper::class;
    }

    /**
     * Creates a new Specimen instance and returns it.
     *
     * @param array $data The data attributes to use, if any.
     * @return Specimen
     */
    public static function make($data = [])
    {
        return (new Specimen())->withData($data);
    }

}
