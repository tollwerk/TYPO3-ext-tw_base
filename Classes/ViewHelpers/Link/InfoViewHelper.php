<?php

namespace Tollwerk\TwBase\ViewHelpers\Link;

use Tollwerk\TwBase\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Decodes a typolink string and returns information about the link
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
     * @param array $arguments Arguments
     * @param \Closure $renderChildrenClosure Children rendering closure
     * @param RenderingContextInterface $renderingContext Rendering context
     *
     * @return mixed|string Output
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
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
     * Process a link of type 'tel'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processTelType(array &$currentLinkParts)
    {
        $currentLinkParts['url']['intl'] = preg_match(
            '/^(?:\+|(?:00))(\d+)$/',
            $currentLinkParts['url']['number'],
            $local
        );

        // If the number is in international format and the static info tables extension is available
        if ($currentLinkParts['url']['intl'] && ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $objectManager     = GeneralUtility::makeInstance(ObjectManager::class);
            $countryRepository = $objectManager->get(CountryRepository::class);
            $countries         = $countryRepository->findByIntlPhoneNumber($local[1]);
            if ($countries) {
                $currentLinkParts['url']['countries'] = $countries;
            }
        }
    }

    /**
     * Process a link of type 'url'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processUrlType(array &$currentLinkParts)
    {
        $currentLinkParts['url'] = array_replace($currentLinkParts['url'], parse_url($currentLinkParts['url']['url']));
    }

    /**
     * Process a link of type 'page'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processPageType(array &$currentLinkParts)
    {
        $currentLinkParts['url'] = [page => $GLOBALS['TSFE']->sys_page->getPage_noCheck($currentLinkParts['url']['pageuid'])];
    }

    /**
     * Process a link of type 'email'
     *
     * @param array $currentLinkParts Current URL parts
     */
    protected static function processEmailType(array &$currentLinkParts)
    {
        list($currentLinkParts['url']['user'], $currentLinkParts['url']['host']) = explode(
            '@',
            $currentLinkParts['url']['email']
        );
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
