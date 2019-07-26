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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Shared methods for icon viewhelpers
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
trait IconViewHelperTrait
{
    /**
     * Icon root paths
     *
     * @var string[]|null
     */
    protected static $iconRootPaths = null;

    /**
     * Find an icon and return the absolute icon path
     *
     * @param string $icon Icon name
     *
     * @return string Icon file path
     * @throws InvalidConfigurationTypeException
     * @throws \OutOfBoundsException If the icon is unknown
     */
    protected function getIconFile(string $icon): string
    {
        // Search for the icon in the given icon root path order
        foreach ($this->getIconRootPaths() as $iconRootPath) {
            $iconFile = GeneralUtility::getFileAbsFileName($iconRootPath.$icon);
            if (is_file($iconFile)) {
                return $iconFile;
            }
        }

        throw new \OutOfBoundsException($icon, 1549185715);
    }

    /**
     * Return the list of icon root paths
     *
     * @return string[] Icon root paths
     * @throws InvalidConfigurationTypeException
     */
    protected function getIconRootPaths(): array
    {
        if (self::$iconRootPaths === null) {
            self::$iconRootPaths  = [];
            $objectManager        = GeneralUtility::makeInstance(ObjectManager::class);
            $configurationManager = $objectManager->get(ConfigurationManager::class);
            $settings             = $configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'TwBase'
            );
            self::$iconRootPaths  = array_map(
                function($rootPath) {
                    return rtrim($rootPath, '/').'/';
                },
                GeneralUtility::trimExplode(',', $settings['icons']['iconRootPath'], true)
            );
        }

        return self::$iconRootPaths;
    }
}
