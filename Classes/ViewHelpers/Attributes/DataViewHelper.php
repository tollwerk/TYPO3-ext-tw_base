<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Attributes
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

namespace Tollwerk\TwBase\ViewHelpers\Attributes;

use Closure;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render a list of HTML data attributes to be added to an element
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Attributes
 */
class DataViewHelper extends ListViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Render
     *
     * @param array $arguments                            Arguments
     * @param Closure $renderChildrenClosure              Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     *
     * @return mixed|string Output
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $dataAttributes    = self::renderAttributes($arguments['attributes'] ?? [],
            $arguments['nonEmptyAttributes'] ?? [], true);
        $excludeAttributes = is_array($arguments['exclude']) ?
            $arguments['exclude'] : GeneralUtility::trimExplode(',', $arguments['exclude'], true);
        if (count($excludeAttributes)) {
            $dataAttributes = array_diff_key($dataAttributes, array_flip($excludeAttributes));
        }
        $attributes  = [];
        $returnArray = $arguments['returnArray'];
        foreach ($dataAttributes as $name => $value) {
            $attributes["data-$name"] = $returnArray ?
                (strlen(trim($value)) ? trim($value) : null) :
                self::renderAttribute("data-$name", $value, false);
        }
        $attributes = array_filter($attributes);

        return $returnArray ? $attributes : ((count($attributes) ? ' ' : '').implode(' ', $attributes));
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument('attributes', 'array',
            'Arbitrary number of values to be rendered as HTML data attributes', false, []);
        $this->registerArgument('exclude', 'mixed',
            'List of variables to be excluded from the data attributes', false, []);
    }
}
