<?php

namespace Tollwerk\TwBase\Utility;

/**
 * cURL utility for making HTTP requests
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class CurlUtility
{
    /**
     * SSL certificate validation
     *
     * @var boolean
     */
    protected static $_verify = true;
    /**
     * GET request
     *
     * @var string
     */
    const GET = 'GET';
    /**
     * POST request
     *
     * @var string
     */
    const POST = 'POST';
    /**
     * DELETE request
     *
     * @var string
     */
    const DELETE = 'DELETE';
    /**
     * PUT request
     *
     * @var string
     */
    const PUT = 'PUT';

    /**
     * Enable / disable the SSL certificate validation
     *
     * @param boolean $verify Validate SSL certificates
     *
     * @return boolean Validate SSL certificates
     */
    public static function setVerify($verify = true)
    {
        self::$_verify = (boolean)$verify;

        return self::$_verify;
    }

    /**
     * Make a HTTP request
     *
     * @param string $url     Endpoint / URL
     * @param array $header   Header
     * @param string $method  Method
     * @param string $body    Body
     * @param boolean $debug  Output debugging information
     * @param int $httpStatus HTTP status code
     *
     * @return string Result
     */
    public static function httpRequest(
        $url,
        array $header = [],
        $method = self::GET,
        $body = null,
        $debug = false,
        &$httpStatus = 0
    ) {
        $httpStatus = 0;
        $curl       = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        if (!self::$_verify) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($body) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, strval($body));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($header, 'Content-Type: text/xml;charset=utf-8'));
        }

        $data       = curl_exec($curl);
        $info       = curl_getinfo($curl);
        $httpStatus = $info['http_code'];

        // Ggf. Debugging-Ausgabe
        if ($debug) {
            $info['method'] = $method;
            $info['body']   = strval($body);
            print_r($header);
            print_r($info);
        }

        curl_close($curl);

        return $data;
    }
}
