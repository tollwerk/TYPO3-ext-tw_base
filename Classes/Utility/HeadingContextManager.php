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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Heading context manager
 */
class HeadingContextManager implements SingletonInterface
{
    /**
     * Visual headline types
     */
    const VISUAL_TYPE_XXLARGE = 'xx-large';
    const VISUAL_TYPE_XLARGE = 'x-large';
    const VISUAL_TYPE_LARGE = 'large';
    const VISUAL_TYPE_MEDIUM = 'medium';
    const VISUAL_TYPE_SMALL = 'small';
    const VISUAL_TYPE_XSMALL = 'x-small';
    const VISUAL_TYPES = [
        1 => self::VISUAL_TYPE_XXLARGE,
        2 => self::VISUAL_TYPE_XLARGE,
        3 => self::VISUAL_TYPE_LARGE,
        4 => self::VISUAL_TYPE_MEDIUM,
        5 => self::VISUAL_TYPE_SMALL,
        6 => self::VISUAL_TYPE_XSMALL,
    ];
    /**
     * Current headline level
     *
     * @var int
     */
    protected $currentLevel = 0;
    /**
     * Current headline type
     *
     * @var int
     */
    protected $currentType = 0;
    /**
     * Maximum rendered level
     *
     * @var int
     */
    protected $maxLevel = 0;
    /**
     * Heading contexts
     *
     * @var HeadingContext[]
     */
    protected $contexts = [];

    /**
     * Set up a new headline context
     *
     * @param int $level      Desired headline level
     * @param int $visualType Visual headline type
     * @param string $content Heading content (for logging purposes only)
     *
     * @return HeadingContext Heading context
     */
    public function setupContext(int $level = null, int $visualType = null, string $content = ''): HeadingContext
    {
        $level      = intval($level);
        $afterLevel = max(1, $this->currentLevel);
        $hidden     = ($level >= 100);
        $level      = ($level >= 100) ? 0 : $level;
        $error      = null;

        // debug([$level, $this->currentLevel], 'Setup context');

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
        $this->currentType = max(1, intval($visualType) ?: $level);
        if ($this->currentType <= count(self::VISUAL_TYPES)) {
            $visualType = self::VISUAL_TYPES[$this->currentType];
        }

        $this->currentLevel = $level;
        $headingContext     = GeneralUtility::makeInstance(
            HeadingContext::class,
            $level,
            $visualType,
            $afterLevel,
            $hidden,
            $error
        );
        $this->contexts[]   = $headingContext;

        return $headingContext;
    }

    /**
     * Tear down the last headline context
     *
     * @param HeadingContext $headingContext Heading context to tear down
     */
    public function tearDownContext(HeadingContext $headingContext): void
    {
        $this->currentLevel = $headingContext->getAfterLevel();
//        debug($this->currentLevel, 'Tear down');
    }

    /**
     * Return a hash representing the current heading context
     *
     * @return string Heading context hash
     */
    public function getCurrentContext(): string
    {
        return count($this->contexts) ? spl_object_hash($this->contexts[count($this->contexts) - 1]) : '';
    }

    /**
     * Return the current heading level
     *
     * @return int Current heading level
     */
    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    /**
     * Return the current heading type
     *
     * @return int Current heading type
     */
    public function getCurrentType(): int
    {
        return $this->currentType;
    }

    /**
     * Restore a heading context
     *
     * @param string $restoreContext Heading context descriptor
     * @param bool $restoreRoot      Restore the root level if requested
     *
     * @return string|null Current heading context
     */
    public function restoreContext(string $restoreContext, bool $restoreRoot = false): ?string
    {
        $restoreContext = trim($restoreContext);
        $restoreRoot    = $restoreRoot || !$this->currentLevel;
        if (!strlen($restoreContext)) {
            $this->currentLevel = $restoreRoot ? 0 : 1;
            $this->contexts     = [];

            return null;
        }

        while (count($this->contexts) && (spl_object_hash($this->contexts[count($this->contexts) - 1]) != $restoreContext)) {
            $this->currentLevel = array_pop($this->contexts)->getAfterLevel();
        }

        return $this->currentLevel;
    }
}
