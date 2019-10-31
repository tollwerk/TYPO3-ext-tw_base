<?php
/**
 * RWS Relaunch
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Classes\ViewHelpers\Format
 * @author     Klaus Fiedler <klaus@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
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

namespace Tollwerk\TwBase\ViewHelpers\Format;


use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use Tollwerk\TwBase\Utility\LocalizationUtility;

/**
 * AgeViewHelper
 *
 * Return the humand readable age of a timestamp,
 * e.g. "One hour ago"
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Classes\ViewHelpers\Format
 */
class AgeViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'mixed', 'The value to output');
    }

    /**
     * Format a timestamp as human readable string like "one hour ago"
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $then = intval($renderChildrenClosure());
        $now = time();
        $seconds = $now - $then;

        if ($seconds <= 0) {
            return LocalizationUtility::translate('age.right_now', 'TwBase');
        }

        // Return seconds
        if ($seconds < 60) {
            return ($seconds > 1 ? LocalizationUtility::translate('age.seconds', 'TwBase', array($seconds)) : LocalizationUtility::translate('age.second', 'TwBase', array($seconds)));
        }

        // Return minutes
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return ($minutes > 1 ? LocalizationUtility::translate('age.minutes', 'TwBase', array($minutes)) : LocalizationUtility::translate('age.minute', 'TwBase', array($minutes)));
        }

        // Return hours
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return ($hours > 1 ? LocalizationUtility::translate('age.hours', 'TwBase', array($hours)) : LocalizationUtility::translate('age.hour', 'TwBase', array($hours)));
        }

        // Return days
        $days = floor($hours / 24);
        if ($days < 7) {
            return ($days > 1 ? LocalizationUtility::translate('age.days', 'TwBase', array($days)) : LocalizationUtility::translate('age.day', 'TwBase', array($days)));
        }

        // Return weeks
        $weeks = floor($days / 7);
        if ($weeks < 4) {
            return ($weeks > 1 ? LocalizationUtility::translate('age.weeks', 'TwBase', array($weeks)) : LocalizationUtility::translate('age.week', 'TwBase', array($weeks)));
        }

        // Return months
        $months = floor($weeks / 4);
        if ($months < 12) {
            return ($months > 1 ? LocalizationUtility::translate('age.months', 'TwBase', array($months)) : LocalizationUtility::translate('age.month', 'TwBase', array($months)));
        }

        // Return years
        $years = floor($months / 12);
        return ($years > 1 ? LocalizationUtility::translate('age.years', 'TwBase', array($years)) : LocalizationUtility::translate('age.year', 'TwBase', array($years)));
    }
}