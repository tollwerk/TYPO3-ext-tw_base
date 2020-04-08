<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwTollwerk
 * @subpackage Tollwerk\TwTollwerk\Domain\Provider
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Joschi Kuphal <joschi@tollwerk.de>
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

namespace Tollwerk\TwBase\Domain\Provider;

use Tollwerk\TwBase\Utility\PageTitleUtility;
use TYPO3\CMS\Core\PageTitle\RecordPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * SEO Page Title Provider
 *
 * @package    Tollwerk\TwTollwerk
 * @subpackage Tollwerk\TwTollwerk\Domain\Provider
 */
class SeoPageTitleProvider extends RecordPageTitleProvider
{
    /**
     * Page title separator
     *
     * @var string
     */
    protected $pageTitleSeparator = ':';
    /**
     * Page title first
     *
     * @var bool
     */
    protected $pageTitleFirst = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $objectManager            = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager     = $objectManager->get(ConfigurationManager::class);
        $settings                 = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $this->pageTitleSeparator = $settings['config.']['pageTitleSeparator'];
        $this->pageTitleFirst     = boolval($settings['config.']['pageTitleFirst']);
    }

    /**
     * Return the page title
     *
     * @return string
     */
    public function getTitle(): string
    {
        $title = '';

        // Run through all registered providers
        foreach (array_reverse(PageTitleUtility::getTitleProviders()) as $titleProviderClass) {
            if ($titleProviderClass !== __CLASS__) {
                $titleProvider = GeneralUtility::makeInstance($titleProviderClass);
                $title         = trim($titleProvider->getTitle());
                if (!empty($title)) {
                    break;
                }
            }
        }

        $pageTitle         = trim($GLOBALS['TSFE']->page['title']);
        $title             = $title ?: $pageTitle;
        $seoPageTitle      = trim($GLOBALS['TSFE']->page['tx_twbase_seo_title']);
        $pageTitlePosition = stripos($seoPageTitle, $pageTitle);
        if ($pageTitlePosition !== false) {
            $seoPageTitle = substr($seoPageTitle, 0, $pageTitlePosition).$title.
                            substr($seoPageTitle, $pageTitlePosition + strlen($pageTitle));
        }

        return $seoPageTitle;
    }
}
