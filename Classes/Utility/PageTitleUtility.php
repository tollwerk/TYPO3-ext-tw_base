<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
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

namespace Tollwerk\TwBase\Utility;

use Tollwerk\TwBase\Domain\Provider\FlexPageTitleProvider;
use TYPO3\CMS\Core\PageTitle\PageTitleProviderInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Page title utility
 */
class PageTitleUtility
{
    /**
     * Title providers
     *
     * @var string[]
     */
    protected static $titleProviders = null;

    /**
     * Set the page title via the custom page title provider
     *
     * @param string $title              New page title (may contain placeholder)
     * @param array $replacementProvider List of page title provider keys to use as replacement
     *
     * @return string New page title (with replacements)
     * @throws Exception
     */
    public static function setPageTitle(string $title, array $replacementProvider = []): string
    {
        $replacement = self::getReplacement($replacementProvider);
        $title       = $replacement ? sprintf($title, $replacement) : $title;

        return GeneralUtility::makeInstance(FlexPageTitleProvider::class)->setTitle($title);
    }

    /**
     * Return the first non-empty page title provider title
     *
     * @param array $replacementProviders Ordered list of page title providers
     *
     * @return string|null Replacement title
     * @throws Exception
     */
    protected static function getReplacement(array $replacementProviders): ?string
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $providers     = static::getTitleProviders();
        foreach ($replacementProviders as $replacementProviderKey) {
            if (!empty($providers[$replacementProviderKey])) {
                $replacementProvider = $objectManager->get($providers[$replacementProviderKey]);
                if (is_a($replacementProvider, PageTitleProviderInterface::class)) {
                    $replacementProviderTitle = trim($replacementProvider->getTitle());
                    if (strlen($replacementProviderTitle)) {
                        return $replacementProviderTitle;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Read and return all registered page title providers
     *
     * @return array Page title providers
     */
    public static function getTitleProviders(): array
    {
        if (static::$titleProviders === null) {
            $typoscriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $config            = $typoscriptService->convertTypoScriptArrayToPlainArray(
                $GLOBALS['TSFE']->config['config'] ?? []
            );

            static::$titleProviders = [];
            foreach ($config['pageTitleProviders'] ?? [] as $key => $properties) {
                if (!empty($properties['provider']) && class_exists($properties['provider'])) {
                    static::$titleProviders[$key] = $properties['provider'];
                }
            }
        }

        return static::$titleProviders;
    }
}
