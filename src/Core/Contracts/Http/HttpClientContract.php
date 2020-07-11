<?php

namespace Stillat\Meerkat\Core\Contracts\Http;

use Stillat\Meerkat\Core\Http\HttpResponse;

interface HttpClientContract
{

    /**
     * Sets the request timeout, in seconds.
     *
     * @param integer $timeout
     */
    public function setRequestTimeOut($timeout);

    /**
     * Issues a POST request and returns the server's response.
     *
     * @param $url
     * @param array $data
     * @param string $referer
     * @return HttpResponse
     */
    public function post($url, $data = [], $referer = '');

    /**
     * Issues a GET request and returns the server's response.
     *
     * @param $url
     * @param array $data
     * @param string $referer
     * @return mixed
     */
    public function get($url, $data = [], $referer = '');

}
