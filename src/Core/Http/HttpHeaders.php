<?php

namespace Stillat\Meerkat\Core\Http;

/**
 * Class HttpHeaders
 *
 * Represents a collection of request HTTP headers.
 *
 * @package Stillat\Meerkat\Core\Http
 * @since 2.0.0
 */
class HttpHeaders
{

    /**
     * A collection of raw header values.
     *
     * @var array
     */
    protected $rawHeaders = [];

    /**
     * A collection of key/value header pairs.
     *
     * @var array
     */
    protected $keyedHeaders = [];

    /**
     * Parses the HTTP headers.
     *
     * @param $headerContent string The header content.
     */
    public function parse($headerContent)
    {
        $splitHeaders = explode("\r\n", trim($headerContent));

        foreach ($splitHeaders as $header) {
            if (mb_strlen($header) > 0) {
                $pieces = [];
                preg_match('/^([^:]+):(.*)$/', $header, $pieces);

                if (is_array($pieces) && count($pieces) >= 3) {
                    $this->keyedHeaders[mb_strtolower($pieces[1])] = $pieces[2];
                }

                $this->rawHeaders[] = $header;
            }
        }
    }

    /**
     * Determines if the request was sent with a chunked transfer encoding.
     *
     * @return bool
     */
    public function isChunked()
    {
        $transferValue = $this->getHeaderValue('transfer-encoding');

        if ($transferValue == null) {
            return false;
        }

        if (mb_strtolower(trim($transferValue)) == 'chunked') {
            return true;
        }

        return false;
    }

    /**
     * Gets a header's value.
     *
     * @param $header string The header to locate.
     * @param null $default An optional default value.
     * @return mixed|null
     */
    public function getHeaderValue($header, $default = null)
    {
        if ($this->hasHeader($header)) {
            return $this->keyedHeaders[mb_strtolower($header)];
        }

        return $default;
    }

    /**
     * Checks if a header with the provided name exists.
     *
     * @param $header string The header to test.
     * @return bool
     */
    public function hasHeader($header)
    {
        return array_key_exists(mb_strtolower($header), $this->keyedHeaders);
    }

}
