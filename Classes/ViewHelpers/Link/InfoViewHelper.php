<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Link
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

namespace Tollwerk\TwBase\ViewHelpers\Link;

use Closure;
use Tollwerk\TwBase\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Decodes a typolink string and returns information about the link
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Link
 */
class InfoViewHelper extends AbstractViewHelper
{
    /**
     * Enable static rendering
     */
    use CompileWithRenderStatic;

    /**
     * Render
     *
     * @param array $arguments                            Arguments
     * @param Closure $renderChildrenClosure              Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     *
     * @return array Link information
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): array {
        $currentLinkParts           = GeneralUtility::makeInstance(TypoLinkCodecService::class)
                                                    ->decode($arguments['typolink']);
        $currentLinkParts['params'] = $currentLinkParts['additionalParams'];
        unset($currentLinkParts['additionalParams']);

        if (!empty($currentLinkParts['url'])) {
            $linkService              = GeneralUtility::makeInstance(LinkService::class);
            $data                     = $linkService->resolve($currentLinkParts['url']);
            $currentLinkParts['type'] = $data['type'];
            unset($data['type']);
            $currentLinkParts['url'] = $data;

            // Fix tel links
            if ($currentLinkParts['type'] === 'tel') {
                $currentLinkParts['url'] = ['number' => $data['value']];
            } else {
                $currentLinkParts['url'] = $data;
            }
        }

        // Type dependent processing
        switch ($currentLinkParts['type']) {
            case 'page':
                self::processPageType($currentLinkParts);
                break;
            case 'url':
                self::processUrlType($currentLinkParts);
                break;
            case 'email':
                self::processEmailType($currentLinkParts);
                break;
            case 'tel':
                self::processTelType($currentLinkParts);
                break;
        }

        return $currentLinkParts;
    }

    /**
     * Process a link of type 'page'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processPageType(array &$currentLinkParts): void
    {
        $currentLinkParts['url'] = ['page' => $GLOBALS['TSFE']->sys_page->getPage_noCheck($currentLinkParts['url']['pageuid'])];
    }

    /**
     * Process a link of type 'url'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processUrlType(array &$currentLinkParts): void
    {
        $currentLinkParts['url'] = array_replace($currentLinkParts['url'], parse_url($currentLinkParts['url']['url']));
    }

    /**
     * Process a link of type 'email'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processEmailType(array &$currentLinkParts): void
    {
        list($currentLinkParts['url']['user'], $currentLinkParts['url']['host']) = explode(
            '@',
            $currentLinkParts['url']['email']
        );
    }

    /**
     * Process a link of type 'tel'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processTelType(array &$currentLinkParts): void
    {
        $currentLinkParts['url']['intl'] = preg_match(
            '/^(?:\+|(?:00))(\d+)$/',
            $currentLinkParts['url']['number'],
            $local
        );

        // If the number is in international format and the static info tables extension is available
        try {
            if ($currentLinkParts['url']['intl'] && ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $objectManager     = GeneralUtility::makeInstance(ObjectManager::class);
                $countryRepository = $objectManager->get(CountryRepository::class);
                $countries         = $countryRepository->findByIntlPhoneNumber($local[1]);
                if ($countries) {
                    $currentLinkParts['url']['countries'] = $countries;
                }
            }
        } catch (Exception $e) {
            // Ignore
        }
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('typolink', 'string', 'Typolink string to return information about', true);
    }
}
