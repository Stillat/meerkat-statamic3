<?php

namespace Stillat\Meerkat\Core\Http;

use Stillat\Meerkat\Core\Contracts\Http\HttpClientContract;

/**
 * Class Client
 *
 * A simple HTTP Client implementation which does not require direct dependencies.
 *
 * Implementing systems may provide their own HttpClient implementations by implementing the HttpClientContract.
 *
 * @package Stillat\Meerkat\Core\Http
 * @since 2.0.0
 */
class Client implements HttpClientContract
{

    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';

    /**
     * The request timeout value.
     *
     * @var int The number of seconds before a timeout is triggered.
     */
    protected $requestTimeoutInSeconds = 30;

    /**
     * Sets the request timeout, in seconds.
     * @param integer $timeout
     */
    public function setRequestTimeOut($timeout)
    {
        $this->requestTimeoutInSeconds = $timeout;
    }

    /**
     * Issues a POST request and returns the server's response.
     *
     * @param $url
     * @param array $data
     * @param string $referer
     * @return HttpResponse
     */
    public function post($url, $data = [], $referer = '')
    {
        return $this->makeRequest(Client::HTTP_POST, $url, $data, $referer);
    }

    /**
     * Issues an HTTP request with the provided information.
     *
     * @param $httpVerb
     * @param $url
     * @param $data
     * @param $referer
     * @return HttpResponse
     */
    private function makeRequest($httpVerb, $url, $data, $referer)
    {
        $httpResponse = new HttpResponse();

        $requestData = $this->buildQueryData($data);
        $requestUrl = parse_url($url);

        $requestPort = 80;
        $host = $requestUrl['host'];
        $path = $requestUrl['path'];
        $requestHost = $host;

        if (array_key_exists('scheme', $requestUrl) && $requestUrl['scheme'] == 'https') {
            $requestPort = 443;
            $requestHost = 'ssl://' . $requestHost;
        }

        $errorNumber = null;
        $errorString = null;
        $requestResult = '';

        try {
            $fp = fsockopen($requestHost, $requestPort, $errorNumber, $errorString, $this->requestTimeoutInSeconds);

            if ($fp) {
                fputs($fp, $httpVerb . " $path HTTP/1.1\r\n");
                fputs($fp, "Host: " . $host . "\r\n");

                if ($referer != '') {
                    fputs($fp, "Referer: $referer\r\n");
                }

                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: " . mb_strlen($requestData) . "\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $requestData);

                $requestResult = '';

                while (!feof($fp)) {
                    $requestResult .= fgets($fp, 128);
                }

                $httpResponse->wasSuccess = true;
                $httpResponse->status = 'ok';
            } else {
                $httpResponse->wasSuccess = false;
                $httpResponse->status = 'err';
                $httpResponse->error = $errorString;
                $httpResponse->errorNumber = $errorNumber;
            }

            fclose($fp);
        } catch (\Exception $e) {
            $httpResponse->wasSuccess = false;
            $httpResponse->wasClientFailure = true;
            $httpResponse->error = $e->getMessage();
        }

        if ($httpResponse->wasSuccess) {
            $requestResult = explode("\r\n\r\n", $requestResult, 2);

            $header = isset($requestResult[0]) ? $requestResult[0] : '';
            $content = isset($requestResult[1]) ? $requestResult[1] : '';

            $httpResponse->header = $header;
            $httpResponse->content = $content;
        }

        $headers = new HttpHeaders();
        $headers->parse($httpResponse->header);

        if ($headers->isChunked()) {
            $httpResponse->content = $this->decode($httpResponse->content);
        }

        $httpResponse->headers = $headers;

        return $httpResponse;
    }

    /**
     * Returns a URL-encoded string for the provided data.
     *
     * @param $data
     * @return string
     */
    private function buildQueryData($data)
    {
        return http_build_query($data);
    }

    private function decode($chunk)
    {
        $pos = 0;
        $len = strlen($chunk);
        $dechunk = null;

        while (($pos < $len)
            && ($chunkLenHex = substr($chunk, $pos, ($newlineAt = strpos($chunk, "\n", $pos + 1)) - $pos))) {
            if (!$this->isHex($chunkLenHex)) {
                trigger_error('Value is not properly chunk encoded', E_USER_WARNING);
                return $chunk;
            }

            $pos = $newlineAt + 1;
            $chunkLen = hexdec(rtrim($chunkLenHex, "\r\n"));
            $dechunk .= substr($chunk, $pos, $chunkLen);
            $pos = strpos($chunk, "\n", $pos + $chunkLen) + 1;
        }
        return $dechunk;
    }

    private function isHex($hex)
    {
        // regex is for weenies
        $hex = strtolower(trim(ltrim($hex, "0")));
        if (empty($hex)) {
            $hex = 0;
        };
        $dec = hexdec($hex);
        return ($hex == dechex($dec));
    }

    public function get($url, $data = [], $referer = '')
    {
        return $this->makeRequest(Client::HTTP_GET, $url, $data, $referer);
    }

}
