<?php
/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Controller
 * @author     Klaus Fiedler <klaus@tollwerk.de>
 * @copyright  Copyright © 2020 Klaus Fiedler <klaus@tollwerk.de>
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Klaus Fiedler <klaus@tollwerk.de>
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

namespace Tollwerk\TwBase\Controller;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * AjaxController
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Controller
 */
class AjaxController extends ActionController
{
    const STATUS_ERROR = 500;
    const STATUS_NO_METHOD = 400;
    const STATUS_SUCCESS = 200;

    /**
     * Available functions registered registered by hook
     * @var array
     */
    protected $registeredFunctions = [];

    /**
     * Return a JSON encoded array containing a status and the method result
     *
     * @param string $status
     * @param mixed $result
     * @param \Exception|null $exception
     *
     * @return string
     */
    private function createJsonResponse(string $status, $result = null, \Exception $exception = null)
    {
        $return = [
            'status' => intval($status),
            'result' => $result
        ];

        if ($exception) {
            $devIpMask = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
            if (in_array($_SERVER['REMOTE_ADDR'], $devIpMask)) {
                $return['exception'] = [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ];
            }
        }

        $GLOBALS['TSFE']->setContentType('text/json');
        return json_encode($return);
    }


    /**
     * The central action to call via ajax.
     * Will check if there is a class registered for the current call.
     *
     * Example:
     * When you want to respond to an ajax call named "myAjaxCall" with arguments "x" and "y",
     * the ajax URL would be /?type=4000call=myAjaxTest&args[x]=1&args[y]=2.
     *
     * You have to register a class responsible for this inside ext_localconf.php of your own extension:
     * $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/tw_base']['ajax']['myAjaxTest'] = \Vendor\Extension\YourAjaxClass::class
     *
     * This class must contain a public method with the same name as the ajax call you are registering for,
     * expecting a single parameter of type array. That array will contain the arguments "x" and "y".
     * The function can return anything that can be encoded as JSON string with json_encode().
     * Returning entire extbase objects is likely to fail or exhaust server memory limit!
     *
     * class MyAjaxClass {
     *     public function myAjaxTest($arguments) {
     *         $x = $arguments['x'];
     *         $y = $arguments['y'];
     *         // ...
     *     }
     * }
     *
     * @return string   Returns a JSON string with status information (error, success etc.)
     *                  and the return value of the method registered for the ajax call.
     */
    public function dispatchAction()
    {
        try {
            $functionName = GeneralUtility::_GP('call');
            $arguments = GeneralUtility::_GP('args') ?: [];
            if (array_key_exists($functionName, $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/tw_base']['ajax'])) {
                $_procObj = GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/tw_base']['ajax'][$functionName]);
                if (is_callable([$_procObj, $functionName])) {
                    return $this->createJsonResponse(
                        self::STATUS_SUCCESS,
                        $_procObj->{$functionName}($arguments)
                    );
                };
            }
            return $this->createJsonResponse(self::STATUS_NO_METHOD);
        } catch (\Exception $e) {
            return $this->createJsonResponse(self::STATUS_ERROR, null, $e);
        }
    }
}
