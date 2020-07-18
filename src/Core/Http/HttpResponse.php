<?php

namespace Stillat\Meerkat\Core\Http;

/**
 * Class HttpResponse
 *
 * Represents an HTTP request response.
 *
 * @package Stillat\Meerkat\Core\Http
 * @since 2.0.0
 */
class HttpResponse
{

    /**
     * Indicates if the request failed due to the client implementation.
     *
     * @var bool
     */
    public $wasClientFailure = false;

    /**
     * Indicates if the request failed due a transport-related reason.
     * @var bool
     */
    public $wasSuccess = false;

    /**
     * The request status.
     *
     * @var string
     */
    public $status = 'err';

    /**
     * The raw request header content.
     *
     * @var string
     */
    public $header = '';

    /**
     * The request error message, if any.
     *
     * @var string
     */
    public $error = '';

    /**
     * The request error number, if any.
     *
     * @var int|null
     */
    public $errorNumber = null;

    /**
     * The request content, if any.
     *
     * @var string
     */
    public $content = '';

    /**
     * A collection of headers.
     *
     * @var null|HttpHeaders
     */
    public $headers = null;

}
