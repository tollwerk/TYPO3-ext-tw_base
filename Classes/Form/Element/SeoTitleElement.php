<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Form\Element
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

namespace Tollwerk\TwBase\Form\Element;

use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * SEO title element
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Form\Element
 */
class SeoTitleElement extends InputTextElement
{
    /**
     * This will render a single-line input form field with dynamic max length (depending on current site title)
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     * @throws InvalidConfigurationTypeException
     * @throws Exception
     */
    public function render()
    {
        $objectManager         = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager  = $objectManager->get(ConfigurationManager::class);
        $settings              = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $pageTitleFirst        = boolval($settings['config.']['pageTitleFirst']);
        $contentObjectRenderer = $objectManager->get(ContentObjectRenderer::class);
        $pageTitleSeparator    = $contentObjectRenderer->stdWrap(
            $settings['config.']['pageTitleSeparator'],
            $settings['config.']['pageTitleSeparator.'] ?? []
        ) ?: ': ';

        $maxCharacters = 65 - strlen($pageTitleSeparator);
        $siteTitle     = '';
        try {
            $language     = intval(empty($this->data['databaseRow']['sys_language_uid']) ? 0 : $this->data['databaseRow']['sys_language_uid']);
            $siteFinder   = GeneralUtility::makeInstance(SiteFinder::class);
            $site         = $siteFinder->getSiteByPageId($this->data['vanillaUid']);
            $siteLanguage = $site->getLanguageById($language);
            $siteTitle    = trim($siteLanguage->getWebsiteTitle() ?: $site->getConfiguration()['websiteTitle']);
            if (strlen($siteTitle)) {
                $maxCharacters -= (strlen($siteTitle) + 1);
            }
        } catch (SiteNotFoundException $e) {
            // Skip
        }

        $this->data['parameterArray']['fieldConf']['config']['max'] = $maxCharacters;
        $result                                                     = parent::render();

        if (strlen($siteTitle) && preg_match('#^(<div[^>]+)><div(.+)</div>#s', $result['html'], $match)) {
            $pageTitleSeparator = str_replace(' ', '&nbsp;', htmlspecialchars($pageTitleSeparator));
            if ($pageTitleFirst) {
                $result['html'] = $match[1].' style="display:flex;align-items:center;"><div style="width:480px"'.
                                  $match[2].'<div>'.$pageTitleSeparator.htmlspecialchars($siteTitle).'</div></div>';
            } else {
                $result['html'] = $match[1].' style="display:flex;align-items:center;"><div>'.htmlspecialchars($siteTitle)
                                  .$pageTitleSeparator.'</div><div style="width:480px"'.$match[2].'<div>';
            }
        }

        return $result;
    }
}
