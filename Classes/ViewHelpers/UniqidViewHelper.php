<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
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

namespace Tollwerk\TwBase\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Create a unique ID for a data set
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class UniqidViewHelper extends AbstractViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('prefix', 'string', 'ID prefix', true);
        $this->registerArgument('data', 'mixed', 'Data to create the unique ID from', true);
        $this->registerArgument('prefer', 'string', 'Preferred ID (if not empty)', false);
    }

    /**
     * Render
     *
     * @param array $arguments                            Arguments
     * @param \Closure $renderChildrenClosure             Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     *
     * @return mixed|string Output
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if (!empty($arguments['prefer'])) {
            return $arguments['prefer'];
        }

        // If it's a scalar component value
        if (is_scalar($arguments['data'])) {
            return $arguments['prefix'].substr(md5($arguments['data']), 0, 10);

            // Else if it's an array
        } elseif (is_array($arguments['data'])) {
            return $arguments['prefix'].substr(md5(serialize($arguments['data'])), 0, 10);

            // Else if it's an object
        } elseif (is_object($arguments['data'])) {
            return $arguments['prefix'].substr(md5(spl_object_hash($arguments['data'])), 0, 10);

            // Else: create a random ID
        } else {
            return $arguments['prefix'].substr(md5(rand(0, time())), 0, 10);
        }
    }
}
