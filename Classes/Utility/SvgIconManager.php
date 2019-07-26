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

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * SVG sprite manager
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class SvgIconManager
{
    /**
     * SVG source files
     *
     * @var array
     */
    protected static $sources = [];
    /**
     * SVG use snippets
     *
     * @var array
     */
    protected static $uses = [];

    /**
     * Return an unique use key for the given SVG source
     *
     * @param string $svgSource SVG source file path
     *
     * @return \DOMDocument SVG usage
     */
    public static function useIcon($svgSource): \DOMDocument
    {
        // Create a unique use key
        if (empty(self::$uses[$svgSource])) {
            self::$uses[$svgSource] = self::getUseSource($svgSource, self::getUseKey($svgSource));
        }

        return self::$uses[$svgSource];
    }

    /**
     * Register a SVG source for sprite output and return the use reference key
     *
     * @param string $svgSource SVG source file path
     *
     * @return string|null Use reference key
     */
    public static function useIconReference($svgSource)
    {
        return self::useIcon($svgSource) ? self::getUseKey($svgSource) : null;
    }

    /**
     * Create and return a unique use reference hash for a SVG file
     *
     * @param string $svgSource SVG source file path
     *
     * @return string
     */
    protected static function getUseKey($svgSource)
    {
        return strtolower(pathinfo($svgSource, PATHINFO_FILENAME))
               .substr(md5_file($svgSource), 0, 8);
    }

    /**
     * Prepare and return a single sprite SVG
     *
     * @param string $filename Filename
     * @param string $useKey   Use key
     *
     * @return string Sprite SVG
     */
    protected static function getUseSource($filename, $useKey)
    {
        $width  = $height = 0;
        $svgDom = self::getIcon($filename, $width, $height);
        $svgDom->documentElement->setAttribute('id', $useKey);
        self::$sources[$filename] = $svgDom->saveXML($svgDom->documentElement);
        $svgUse                   = new \DOMDocument();
        $svgUse->loadXML('<svg viewBox="0 0 '.$width.' '.$height.'" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#'.$useKey.'" /></svg>');

        return $svgUse;
    }

    /**
     * Open, pre-process and return an SVG icon
     *
     * @param string $filename Filename
     * @param int $width       Icon width
     * @param int $height      Icon height
     *
     * @return \DOMDocument Icon DOM
     */
    public static function getIcon(string $filename, int &$width = 0, int &$height = 0): \DOMDocument
    {
        $svgDom = new \DOMDocument();
        $svgDom->load($filename);

        // Create the viewBox
        if ($svgDom->documentElement->hasAttribute('viewBox')) {
            list(, , $width, $height) = preg_split('/\s+/', trim($svgDom->documentElement->getAttribute('viewBox')));
        } else {
            $width  = intval($svgDom->documentElement->getAttribute('width'));
            $height = intval($svgDom->documentElement->getAttribute('height'));
            if ($width && $height) {
                $svgDom->documentElement->setAttribute('viewBox', "0 0 $width $height");
            }
        }

        $svgDom->documentElement->setAttribute('width', $width);
        $svgDom->documentElement->setAttribute('height', $height);

//        if ($svgDom->documentElement->hasAttribute('width')) {
//            $svgDom->documentElement->removeAttribute('width');
//        }
//        if ($svgDom->documentElement->hasAttribute('height')) {
//            $svgDom->documentElement->removeAttribute('height');
//        }

        return $svgDom;
    }


    /**
     * Inject an SVG sprite
     *
     * @param array $params
     * @param TypoScriptFrontendController $tsfe TypoScript Frontend Controller
     */
    public function injectSvgSprite(array $params, TypoScriptFrontendController $tsfe)
    {
        // If sprite SVGs have been registered
        if (count(self::$sources)) {
            $sprite = '<!-- Auto-injected SVG sprite --><svg class="hide-element" aria-hidden="true">'.implode(self::$sources).'</svg>';
            $body   = preg_split('/(<body.*?>)/', $tsfe->content, 2, PREG_SPLIT_DELIM_CAPTURE);
            if (count($body) == 3) {
                $body[1]       .= $sprite;
                $tsfe->content = implode('', $body);
            } else {
                $tsfe->content = $sprite.$tsfe->content;
            }
        }
    }
}
