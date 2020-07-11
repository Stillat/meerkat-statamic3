<?php

namespace Stillat\Meerkat\Core\Http;

class HttpResponse
{

    public $wasClientFailure = false;
    public $wasSuccess = false;
    public $status = 'err';
    public $header = '';
    public $error = '';
    public $errorNumber = null;
    public $content = '';

    /**
     * A collection of headers.
     *
     * @var null|HttpHeaders
     */
    public $headers = null;

}
