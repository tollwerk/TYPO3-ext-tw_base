<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
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

namespace Tollwerk\TwBase\ViewHelpers;

use DOMDocument;
use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Extended media view helper
 *
 * Use this viewhelper as a replacement for the standard Fluid <f:media> viewhelper. This one provides support
 * for responsive images
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class MediaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\MediaViewHelper
{
    /**
     * Configuration manager
     *
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * SVG attributes to append (instead of overwrite)
     *
     * @var array
     */
    protected $appendAttributes = ['class'];

    /**
     * Inject the configuration manager
     *
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Inject the object manager
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('srcset', 'mixed', 'Image sizes that should be rendered', false);
        $this->registerArgument(
            'sizes',
            'string',
            'Sizes query for responsive image',
            false,
            '(min-width: %1$dpx) %1$dpx, 100vw'
        );
        $this->registerArgument('breakpoints', 'mixed', 'Image breakpoint specifications or preset key', false);
        $this->registerArgument('picturefill', 'bool',
            'Use rendering suggested by picturefill.js and omit the src attribute (see https://github.com/scottjehl/picturefill#the-gotchas)',
            false,
            false
        );
        $this->registerArgument('responsive', 'bool', 'Render responsive image', false, true);
        $this->registerArgument('lazyload', 'bool', 'Use lazyloading', false, false);
        $this->registerArgument('inline', 'bool', 'Inline the image using a data URI', false, false);
        $this->registerArgument('skipConverter', 'mixed', 'File converters that should be skipped', false, []);
        $this->registerArgument('noTitle', 'bool', 'Don\'t add a title attribute', false, false);
    }

    /**
     * Render an image element
     *
     * @param FileInterface $image       Image reference
     * @param string $width              Image width
     * @param string $height             Image height
     * @param string|null $fileExtension File extension
     *
     * @return string Rendered <img> or <picture> element
     * @throws Exception
     */
    protected function renderImage(FileInterface $image, $width, $height, ?string $fileExtension)
    {
        // Disable the title attribute if necessary
        if ($this->arguments['noTitle']) {
            $tag = new TagBuilder($this->tagName);
            $tag->ignoreAttribute('title', true);
            $tag->addAttributes($this->tag->getAttributes());
            $this->setTagBuilder($tag);
        }

        // If the image shouldn't be inlined
        if (!$this->arguments['inline']) {
            $activeConverters = $this->getActiveConverters($image);

            // If the image should be rendered responsively (either because there are breakpoints / several sizes
            // configured or because there are active converters that should be applied)
            if ($this->arguments['responsive'] || count($activeConverters)) {
                // Determine the breakpoint specifications or preset to use
                $breakpoints = $this->getBreakpointSpecifications($this->arguments['breakpoints']);

                // If there are breakpoint specifications available: Render as <picture> element
                if ((!empty($breakpoints) || count($activeConverters))
                    && $this->getResponsiveImagesUtility()->canPicture($image)
                ) {
                    return $this->renderPicture($image, $width, $height, $breakpoints, $activeConverters);

                    // If a source set can be used: Render with srcset attribute
                } elseif ($this->arguments['srcset'] && $this->getResponsiveImagesUtility()->canSrcset($image)) {
                    return $this->renderImageSrcset($image, $width, $height);
                }
            }

            // If the image should only be lazyloaded
            if ($this->arguments['lazyload']) {
                return $this->renderLazyloadImage($image, $width, $height);
            }
        }

        // Fall back to a simple image
        return $this->renderSimpleImage($image, $width, $height);
    }

    /**
     * Return a list of active converters
     *
     * @param FileInterface $image Image reference
     *
     * @return array Active converters
     * @throws Exception
     */
    protected function getActiveConverters(FileInterface $image): array
    {
        $skipConverter       = array_filter(
            is_array($this->arguments['skipConverter']) ?
                $this->arguments['skipConverter'] :
                GeneralUtility::trimExplode(',', $this->arguments['skipConverter'], true)
        );
        $activeConverters    = [];
        $availableConverters = $this->getResponsiveImagesUtility()->getAvailableConverters($image, $skipConverter);
        foreach (array_keys($availableConverters) as $converter) {
            $activeConverters[$converter] = $this->getImageSettings('converters.'.$converter);
        }

        return $activeConverters;
    }

    /**
     * Returns an instance of the responsive images utility
     *
     * This fixes an issue with DI after clearing the cache
     *
     * @return ResponsiveImagesUtility Responsive Image Utility
     */
    protected function getResponsiveImagesUtility(): ResponsiveImagesUtility
    {
        return $this->objectManager->get(ResponsiveImagesUtility::class);
    }

    /**
     * Returns TypoSript settings array for images
     *
     * @param string $key Sub key
     *
     * @return array Image settings
     * @throws Exception
     */
    protected function getImageSettings($key = 'images'): array
    {
        /** @var ImageService $imageService */
        $imageService = GeneralUtility::makeInstance(ImageService::class);

        return $imageService->getImageSettings($key);
    }

    /**
     * Determine the breakpoint specifications or preset to use
     *
     * @param string|array $breakpoints Breakpoint specifications or preset
     *
     * @return array|string Breakpoint specifications
     */
    protected function getBreakpointSpecifications($breakpoints)
    {
        if (!is_array($breakpoints)) {
            $breakpoints = trim($breakpoints);
            $setup       = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            list(, $configsPresets) = GeneralUtility::makeInstance(TypoScriptParser::class)
                                                    ->getVal('lib.contentElement.settings.media.breakpoints', $setup);
            if (!empty($configsPresets['configs.'])
                && !empty($configsPresets['presets.'])
                && array_key_exists($breakpoints, $configsPresets['presets.'])
            ) {
                $configs     = GeneralUtility::trimExplode(',', $configsPresets['presets.'][$breakpoints]);
                $breakpoints = [];
                foreach ($configs as $config) {
                    if (!empty($configsPresets['configs.']["$config."])) {
                        $breakpoints[] = $configsPresets['configs.']["$config."];
                    }
                }
            } else {
                $breakpoints = [];
            }
        }

        return $breakpoints;
    }

    /**
     * Render <picture> element
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     * @param string $height       Image height
     * @param array $breakpoints   Breakpoint specifications
     * @param array $converters    File converters
     *
     * @return string Rendered <picture> element
     * @throws Exception
     */
    protected function renderPicture(
        FileInterface $image,
        $width,
        $height,
        array $breakpoints,
        array $converters
    ): string {
        /**
         * Get crop variants & generate fallback image
         *
         * @var Area $focusArea
         * @var FileInterface $fallbackImage
         * @var CropVariantCollection $cropVariantCollection
         */
        list(, $focusArea, $fallbackImage, $cropVariantCollection) = $this->createAreasAndFallback($image, $width);

        // Generate picture tag
        $this->tag = $this->getResponsiveImagesUtility()->createPictureTag(
            $image,
            $fallbackImage,
            $breakpoints,
            $cropVariantCollection,
            $focusArea,
            null,
            $this->tag,
            $this->arguments['picturefill'],
            $this->arguments['lazyload'] ? $this->getImageSettings() : null,
            $converters
        );

        return $this->tag->render();
    }

    /**
     * Determine the crop & focus areas and create a fallback image
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     *
     * @return array Crop area, focus area, fallback image and crop variant collection
     */
    protected function createAreasAndFallback(FileInterface $image, $width): array
    {
        $cropVariant           = $this->arguments['cropVariant'] ?: 'default';
        $cropString            = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea              = $cropVariantCollection->getCropArea($cropVariant);
        $focusArea             = $cropVariantCollection->getFocusArea($cropVariant);
        $fallbackImage         = $this->generateFallbackImage($image, $width, $cropArea);

        return [$cropArea, $focusArea, $fallbackImage, $cropVariantCollection];
    }

    /**
     * Generates a fallback image for picture and srcset markup
     *
     * @param FileInterface $image Original image
     * @param string $width        Width
     * @param Area $cropArea       Crop area
     *
     * @return FileInterface Fallback image
     */
    protected function generateFallbackImage(FileInterface $image, $width, Area $cropArea): FileInterface
    {
        return $this->getImageService()->applyProcessingInstructions($image, [
            'width' => $width,
            'crop'  => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ]);
    }

    /**
     * Render <img> element with srcset/sizes attributes
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     * @param string $height       Image height
     *
     * @return string Rendered <img> element
     * @throws Exception
     */
    protected function renderImageSrcset(FileInterface $image, $width, $height): string
    {
        // Get crop variants & generate fallback image
        list($cropArea, $focusArea, $fallbackImage) = $this->createAreasAndFallback($image, $width);

        // Generate image tag
        $this->tag = $this->getResponsiveImagesUtility()->createImageTagWithSrcset(
            $image,
            $fallbackImage,
            $this->arguments['srcset'],
            $cropArea,
            $focusArea,
            $this->arguments['sizes'],
            $this->tag,
            $this->arguments['picturefill'],
            $this->arguments['lazyload'] ? $this->getImageSettings() : null
        );

        return $this->tag->render();
    }

    /**
     * Render a lazyloaded <img> element
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     * @param string $height       Image height
     *
     * @return string Rendered <img> element
     * @throws Exception
     */
    protected function renderLazyloadImage(FileInterface $image, $width, $height): string
    {
        $cropVariant            = $this->arguments['cropVariant'] ?: 'default';
        $cropString             = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection  = CropVariantCollection::create((string)$cropString);
        $cropArea               = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width'  => $width,
            'height' => $height,
            'crop'   => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        $imageService           = $this->getImageService();
        $processedImage         = $imageService->applyProcessingInstructions($image, $processingInstructions);
        $imageUri               = $imageService->getImageUri($processedImage);

        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
            }
        }

        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));

        $alt   = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if (empty($this->arguments['alt'])) {
            $this->tag->addAttribute('alt', $alt);
        }
        if (empty($this->arguments['title']) && $title) {
            $this->tag->addAttribute('title', $title);
        }

        // Add the lazyloading attributes
        $this->tag = $this->getResponsiveImagesUtility()->addLazyloadingToImageTag(
            $this->tag,
            $imageUri,
            $this->getImageSettings()
        );

        return $this->tag->render();
    }

    /**
     * Render a simple <img> element
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     * @param string $height       Image height
     *
     * @return string Rendered <img> element
     */
    protected function renderSimpleImage(FileInterface $image, string $width, string $height): string
    {
        // Return a regular image
        $cropVariant            = $this->arguments['cropVariant'] ?: 'default';
        $cropString             = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection  = CropVariantCollection::create((string)$cropString);
        $cropArea               = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width'  => $width,
            'height' => $height,
            'crop'   => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        $imageService           = $this->getImageService();
        $processedImage         = $imageService->applyProcessingInstructions($image, $processingInstructions);

        // If the image should be inlined
        if ($this->arguments['inline']) {
            // If it's an SVG: Return as inline SVG
            if ($processedImage->getExtension() == 'svg') {
                return $this->renderInlineSvg($processedImage);
            }

            // Create data URI
            $imageUri = $this->getResponsiveImagesUtility()->getDataUri(
                $processedImage->getMimeType(),
                $processedImage->getForLocalProcessing()
            );
        } else {
            $imageUri = $imageService->getImageUri($processedImage);
        }

        // Potentially add a focus area attribute
        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
            }
        }
        $this->tag->addAttribute('src', $imageUri);
        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));

        $alt   = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        // Don't add the image default though if an explicit value has been given (see super classes)
        if (empty($this->arguments['alt'])) {
            $this->tag->addAttribute('alt', $alt);
        }
        // Add the image default title if it's not empty and no explicit value was given
        if (empty($this->arguments['title']) && $title) {
            $this->tag->addAttribute('title', $title);
        }

        return $this->tag->render();
    }

    /**
     * Render an inline SVG graphic
     *
     * @param FileInterface $processedFile Processed file
     *
     * @return string Inline SVG
     */
    protected function renderInlineSvg(FileInterface $processedFile): string
    {
        $svgDom = new DOMDocument();
        $svgDom->loadXML($processedFile->getContents());
        foreach ($this->tag->getAttributes() as $name => $value) {
            if ($svgDom->documentElement->hasAttribute($name) && in_array($name, $this->appendAttributes)) {
                $svgDom->documentElement->setAttribute(
                    $name,
                    trim($svgDom->documentElement->getAttribute($name).' '.$value)
                );
            } else {
                $svgDom->documentElement->setAttribute($name, $value);
            }
        }

        return $svgDom->saveXML();
    }
}
