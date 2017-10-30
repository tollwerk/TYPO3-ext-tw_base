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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Headline context manager
 */
class HeadlineContextManager implements SingletonInterface
{
    /**
     * Current headline level
     *
     * @var int
     */
    protected $currentLevel = 0;

    /**
     * Visual headline types
     */
    const VISUAL_TYPE_MAIN = 'main';
    const VISUAL_TYPE_SECONDARY = 'secondary';
    const VISUAL_TYPE_MEDIUM = 'medium';
    const VISUAL_TYPE_SMALL = 'small';
    const VISUAL_TYPE_BODY = 'body';
    const VISUAL_TYPES = [
        1 => self::VISUAL_TYPE_MAIN,
        2 => self::VISUAL_TYPE_SECONDARY,
        3 => self::VISUAL_TYPE_MEDIUM,
        4 => self::VISUAL_TYPE_SMALL,
        5 => self::VISUAL_TYPE_BODY,
    ];

    /**
     * Set up a new headline context
     *
     * @param int $level Desired headline level
     * @param int $visualType Visual headline type
     * @return HeadlineContext Headline context
     */
    public function setupContext($level = null, $visualType = null)
    {
        $level = intval($level);
        $afterLevel = max(1, $this->currentLevel);
        $error = null;

        // If a particular headline level was given
        if ($level > 0) {
            // If headline levels are skipped: Warning
            if (($level - $this->currentLevel) > 1) {
                $error = true;

                if (!empty($GLOBALS['TSFE'])) {
                    /** @var Logger $logger */
                    $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
                    $logger->warning(sprintf(
                        'Page %s: skipping headline level(s) %s',
                        $GLOBALS['TSFE']->id,
                        implode(', ', range(max(1, $this->currentLevel) + 1, $level - 1))
                    ));
                }
            }

            $afterLevel = min($afterLevel, $level);

            // Else if the headline level should be determined automatically
        } else {
            $level = $this->currentLevel + 1;
        }

        // Determine the visual headline type
        $visualType = max(0, min(intval($visualType), count(self::VISUAL_TYPES)));
        $visualType = self::VISUAL_TYPES[$visualType ?: $level];

        $this->currentLevel = $level;
        return GeneralUtility::makeInstance(HeadlineContext::class, $level, $visualType, $afterLevel, $error);
    }

    /**
     * Tear down the last headline context
     *
     * @param HeadlineContext $headlineContext Headline context to tear down
     */
    public function tearDownContext(HeadlineContext $headlineContext)
    {
        $this->currentLevel = $headlineContext->getAfterLevel();
    }
}
