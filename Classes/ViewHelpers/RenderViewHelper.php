<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\Vamoso
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

use Tollwerk\TwBase\Utility\HeadingContextManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Extended render viewhelper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class RenderViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper
{
    /**
     * Initialize all arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'shiftHeadingLevel',
            'int',
            'Temporarily shift the heading level by this number',
            false,
            0
        );
    }

    /**
     * Render
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $shiftHeadingLevel = intval($arguments['shiftHeadingLevel']);
        if ($shiftHeadingLevel > 0) {
            $shiftedRenderChildrenClosure = function() use ($renderChildrenClosure, $shiftHeadingLevel) {
                $headingContextManager = GeneralUtility::makeInstance(HeadingContextManager::class);
                $currentHeadingContext = $headingContextManager->getCurrentContext();
                $currentHeadingLevel   = $headingContextManager->getCurrentLevel();
                $headingContextManager->setupContext($currentHeadingLevel + $shiftHeadingLevel);
                $children = $renderChildrenClosure();
                $headingContextManager->restoreContext($currentHeadingContext, true);

                return $children;
            };
            $renderChildrenClosure        = $shiftedRenderChildrenClosure;
        }

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
