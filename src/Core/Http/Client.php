<?php

namespace Stillat\Meerkat\Core\Http;

use Exception;
use Stillat\Meerkat\Core\Contracts\Http\HttpClientContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\ErrorLogContext;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;

/**
 * Class Client
 *
 * A simple HTTP Client implementation which does not require direct dependencies.
 *
 * Implementing systems may provide their own HttpClient implementations by implementing the HttpClientContract.
 *
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
     *
     * @param  int  $timeout
     */
    public function setRequestTimeOut($timeout)
    {
        $this->requestTimeoutInSeconds = $timeout;
    }

    /**
     * Issues a POST request and returns the server's response.
     *
     * @param  array  $data
     * @param  string  $referer
     * @return HttpResponse
     */
    public function post($url, $data = [], $referer = '')
    {
        return $this->makeRequest(Client::HTTP_POST, $url, $data, $referer);
    }

    /**
     * Issues an HTTP request with the provided information.
     *
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
            $requestHost = 'ssl://'.$requestHost;
        }

        $errorNumber = null;
        $errorString = null;
        $requestResult = '';

        try {
            $fp = fsockopen($requestHost, $requestPort, $errorNumber, $errorString, $this->requestTimeoutInSeconds);

            if ($fp) {
                fwrite($fp, $httpVerb." $path HTTP/1.1\r\n");
                fwrite($fp, 'Host: '.$host."\r\n");

                if ($referer != '') {
                    fwrite($fp, "Referer: $referer\r\n");
                }

                fwrite($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fwrite($fp, 'Content-length: '.mb_strlen($requestData)."\r\n");
                fwrite($fp, "Connection: close\r\n\r\n");
                fwrite($fp, $requestData);

                $requestResult = '';

                while (! feof($fp)) {
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
        } catch (Exception $e) {
            ExceptionLoggerFactory::log($e);
            $httpResponse->wasSuccess = false;
            $httpResponse->wasClientFailure = true;
            $httpResponse->error = $e->getMessage();

            $logContext = new ErrorLogContext();
            $logContext->msg = 'An exception was thrown during the execution of an HTTP Request ('.$httpVerb.'): '.$url;
            $logContext->details = $e->getMessage();

            ExceptionLoggerFactory::log($e);
            LocalErrorCodeRepository::log(ErrorLog::make(Errors::HTTP_CLIENT_REQUEST_FAILED, $logContext));
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
     * @return string
     */
    private function buildQueryData($data)
    {
        return http_build_query($data);
    }

    /**
     * Decodes the provided chunk.
     *
     * @param  string  $chunk The encoded chunk.
     * @return string|null
     */
    private function decode($chunk)
    {
        $pos = 0;
        $len = strlen($chunk);
        $deCoded = null;

        while (($pos < $len)
            && ($chunkLenHex = substr($chunk, $pos, ($newlineAt = strpos($chunk, "\n", $pos + 1)) - $pos))) {
            if (! $this->isHex($chunkLenHex)) {
                trigger_error('Value is not properly chunk encoded', E_USER_WARNING);

                return $chunk;
            }

            $pos = $newlineAt + 1;
            $chunkLen = hexdec(rtrim($chunkLenHex, "\r\n"));
            $deCoded .= substr($chunk, $pos, $chunkLen);
            $pos = strpos($chunk, "\n", $pos + $chunkLen) + 1;
        }

        return $deCoded;
    }

    /**
     * Tests if the provided value is hex.
     *
     * @param  string  $hex Value to test.
     * @return bool
     */
    private function isHex($hex)
    {
        $hex = strtolower(trim(ltrim($hex, '0')));

        if (empty($hex)) {
            $hex = 0;
        }

        $dec = hexdec($hex);

        return $hex == dechex($dec);
    }

    /**
     * Issues a HTTP GET request with the provided data.
     *
     * @param  string  $url The endpoint to request.
     * @param  array  $data Optional query data to send.
     * @param  string  $referer Optional HTTP referer to set.
     * @return mixed|HttpResponse
     */
    public function get($url, $data = [], $referer = '')
    {
        return $this->makeRequest(Client::HTTP_GET, $url, $data, $referer);
    }
}
