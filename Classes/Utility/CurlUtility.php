<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

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
     * SSL certificate validation
     *
     * @var boolean
     */
    protected static $_verify = true;

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
     * @param bool $debug     Output debugging information
     * @param int $httpStatus HTTP status code
     *
     * @return string Result
     */
    public static function httpRequest(
        string $url,
        array $header = [],
        string $method = self::GET,
        string $body = null,
        bool $debug = false,
        int &$httpStatus = 0
    ): string {
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
        $httpStatus = (int)$info['http_code'];

        // Debugging output
        if ($debug) {
            $info['method'] = $method;
            $info['body']   = strval($body);
            debug($header);
            debug($info);
        }

        curl_close($curl);

        return $data;
    }
}
