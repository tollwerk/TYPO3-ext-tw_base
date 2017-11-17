<?php

/**
 * tollwerk
 *
 * @category Jkphl
 * @package Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Utility
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * TCA utility
 */
class TcaUtility
{
    /**
     * Pre-processor for breakpoint specification presets
     *
     * @param array $config Select configuration
     * @return array Modified select Configuration
     */
    public function responsiveImagesBreakpointsSpecifications(array $config)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var BackendConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get(BackendConfigurationManager::class);
        $setup = $configurationManager->getTypoScriptSetup();
        list(, $conf) = GeneralUtility::makeInstance(TypoScriptParser::class)
            ->getVal('lib.contentElement.settings.media.breakpoints.presets', $setup);

        // Run through all defined presets
        foreach ($conf as $key => $configs) {
            $config['items'][] = [
                sprintf('%s (%s)', ucfirst($key), implode(', ', GeneralUtility::trimExplode(',', $configs, true))),
                $key,
            ];
        }

        return $config;
    }
}
