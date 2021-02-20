<?php

namespace Stillat\Meerkat\Core\Logging\Reporters;

use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterContract;

/**
 * Class SpatieRayReporter
 *
 * Sends error information to Spatie Ray, if it is installed in the host project.
 *
 * @link https://spatie.be/products/ray
 * @package Stillat\Meerkat\Core\Logging\Reporters
 * @since 2.3.0
 */
class SpatieRayReporter implements ErrorReporterContract
{

    /**
     * A hash of all previously reported error objects.
     *
     * @var array
     */
    protected $handled = [];

    /**
     * Sends the error object to Ray, if installed in the host project.
     *
     * @param mixed $errorObject The error object.
     */
    public function log($errorObject)
    {
        $hash = spl_object_hash($errorObject);

        if (array_key_exists($hash, $this->handled) === false) {
            $this->handled[$hash] = 1;
        } else {
            return;
        }

        if (function_exists('\ray') && class_exists('\Spatie\Ray\Ray')) {
            \ray($errorObject);
        }
    }

}
