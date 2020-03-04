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

use Tollwerk\TwBase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Age ViewHelper
 *
 * Return the human readable age of a timestamp, e.g. "One hour ago"
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
        $this->registerArgument('duration', 'bool', 'Interpret value as duration');
    }

    /**
     * Format a timestamp as human readable string like "one hour ago"
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Age string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $then = intval($renderChildrenClosure());

        return boolval($arguments['duration']) ? static::formatDuration($then) : static::formatAge($then);
    }

    /**
     * Format as age
     *
     * @param int $then Timestamp
     *
     * @return string Age string
     */
    protected static function formatAge(int $then): string
    {
        $now     = time();
        $seconds = $now - $then;

        if ($seconds <= 0) {
            return LocalizationUtility::translate('age.right_now', 'TwBase');
        }

        // Return seconds
        if ($seconds < 60) {
            return LocalizationUtility::translate(($seconds > 1) ? 'age.seconds' : 'age.second', 'TwBase', [$seconds]);
        }

        // Return minutes
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return LocalizationUtility::translate(($minutes > 1) ? 'age.minutes' : 'age.minute', 'TwBase', [$minutes]);
        }

        // Return hours
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return LocalizationUtility::translate(($hours > 1) ? 'age.hours' : 'age.hour', 'TwBase', [$hours]);
        }

        // Return days
        $days = floor($hours / 24);
        if ($days < 7) {
            return LocalizationUtility::translate(($days > 1) ? 'age.days' : 'age.day', 'TwBase', [$days]);
        }

        // Return weeks
        $weeks = floor($days / 7);
        if ($weeks < 4) {
            return LocalizationUtility::translate(($weeks > 1) ? 'age.weeks' : 'age.week', 'TwBase', [$weeks]);
        }

        // Return months
        $months = floor($weeks / 4);
        if ($months < 12) {
            return LocalizationUtility::translate(($months > 1) ? 'age.months' : 'age.month', 'TwBase', [$months]);
        }

        // Return years
        $years = floor($months / 12);

        return LocalizationUtility::translate(($years > 1) ? 'age.years' : 'age.year', 'TwBase', [$years]);
    }

    /**
     * Format as duration
     *
     * @param int $duration Duration
     *
     * @return string Duration string
     */
    protected static function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return LocalizationUtility::translate('age.right_now', 'TwBase');
        }

        // Return seconds
        if ($seconds < 60) {
            return LocalizationUtility::translate((($seconds > 1) ? 'age.seconds' : 'age.second').'.duration', 'TwBase',
                [$seconds]);
        }

        // Return minutes
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return LocalizationUtility::translate((($minutes > 1) ? 'age.minutes' : 'age.minute').'.duration', 'TwBase',
                [$minutes]);
        }

        // Return hours
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return LocalizationUtility::translate((($hours > 1) ? 'age.hours' : 'age.hour').'.duration', 'TwBase',
                [$hours]);
        }

        // Return days
        $days = floor($hours / 24);
        if ($days < 7) {
            return LocalizationUtility::translate((($days > 1) ? 'age.days' : 'age.day').'.duration', 'TwBase',
                [$days]);
        }

        // Return weeks
        $weeks = floor($days / 7);
        if ($weeks < 4) {
            return LocalizationUtility::translate((($weeks > 1) ? 'age.weeks' : 'age.week').'.duration', 'TwBase',
                [$weeks]);
        }

        // Return months
        $months = floor($weeks / 4);
        if ($months < 12) {
            return LocalizationUtility::translate((($months > 1) ? 'age.months' : 'age.month').'.duration', 'TwBase',
                [$months]);
        }

        // Return years
        $years = floor($months / 12);

        return LocalizationUtility::translate((($years > 1) ? 'age.years' : 'age.year').'.duration', 'TwBase',
            [$years]);
    }
}
