<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Icon
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

namespace Tollwerk\TwBase\ViewHelpers\Icon;

use Tollwerk\TwBase\Utility\SvgIconManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Return an icon use reference string
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Icon
 */
class UserefViewHelper extends AbstractViewHelper
{
    /**
     * Use shared icon viewhelper methods
     */
    use IconViewHelperTrait;

    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('icon', 'string', 'Name of the icon', true);
    }

    /**
     * Return an icon use reference string
     *
     * @return string Rendered icon
     * @throws InvalidConfigurationTypeException
     * @api
     */
    public function render(): string
    {
        try {
            $icon = ucfirst(pathinfo($this->arguments['icon'], PATHINFO_FILENAME)).'.svg';

            return SvgIconManager::useIconReference($this->getIconFile($icon));
        } catch (\OutOfBoundsException $e) {
            return '<!-- Unknown SVG icon "'.$e->getMessage().'" -->';
        }
    }

}
