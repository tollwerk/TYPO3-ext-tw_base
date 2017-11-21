<?php

namespace Tollwerk\TwBase\ViewHelpers;

use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MediaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\MediaViewHelper
{
    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;
    /**
     * SVG attributes to append (instead of overwrite)
     *
     * @var array
     */
    protected $appendAttributes = ['class'];

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('srcset', 'mixed', 'Image sizes that should be rendered.', false);
        $this->registerArgument(
            'sizes',
            'string',
            'Sizes query for responsive image.',
            false,
            '(min-width: %1$dpx) %1$dpx, 100vw'
        );
        $this->registerArgument('breakpoints', 'mixed', 'Image breakpoint specifications or preset key', false);
        $this->registerArgument('picturefill', 'bool', 'Use rendering suggested by picturefill.js', false, true);
        $this->registerArgument('responsive', 'bool', 'Render responsive image', false, true);
        $this->registerArgument('lazyload', 'bool', 'Use lazyloading', false, false);
        $this->registerArgument('inline', 'bool', 'Inline the image using a data URI', false, false);
        $this->registerArgument('skipConverter', 'mixed', 'File converters that should be skipped', false, []);
    }

    /**
     * Render <img> element
     *
     * @param FileInterface $image Image reference
     * @param string $width Image width
     * @param string $height Image height
     * @return string Rendered <img> element
     */
    protected function renderImage(FileInterface $image, $width, $height)
    {
        // If the image shouldn't be inlined
        if (!$this->arguments['inline']) {
            $activeConverters = $this->getActiveConverters($image);

            // If the image should be rendered responsively
            if ($this->arguments['responsive'] || count($activeConverters)) {
                // Determine the breakpoint specifications or preset to use
                $breakpoints = $this->getBreakpointSpecifications($this->arguments['breakpoints']);

                // If there are breakpoint specifications available: Render as <picture> element
                if (!empty($breakpoints) || count($activeConverters)) {
                    return $this->renderPicture($image, $width, $height, $breakpoints, $activeConverters);

                    // Else: Render with srcset?
                } elseif ($this->arguments['srcset'] && $this->getResponsiveImagesUtility()->canSrcset($image)) {
                    return $this->renderImageSrcset($image, $width, $height);
                }
            }

            // If the image should be lazyloaded
            if ($this->arguments['lazyload']) {
                return $this->renderLazyloadImage($image, $width, $height);
            }
        }

        // Return a regular image
        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width' => $width,
            'height' => $height,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        $imageService = $this->getImageService();
        $processedImage = $imageService->applyProcessingInstructions($image, $processingInstructions);

        // If this image should be inlined
        if ($this->arguments['inline']) {
            // SVG: Return as Inline SVG
            if ($processedImage->getExtension() == 'svg') {
                return $this->renderInlineSvg($processedImage);
            }

            $imageUri = $this->getResponsiveImagesUtility()->getDataUri(
                $processedImage->getMimeType(),
                $processedImage->getForLocalProcessing()
            );
        } else {
            $imageUri = $imageService->getImageUri($processedImage);
        }

        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
            }
        }
        $this->tag->addAttribute('src', $imageUri);
        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));

        $alt = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if (empty($this->arguments['alt'])) {
            $this->tag->addAttribute('alt', $alt);
        }
        if (empty($this->arguments['title']) && $title) {
            $this->tag->addAttribute('title', $title);
        }

        return $this->tag->render();
    }

    /**
     * Return a list of active converters
     *
     * @param FileInterface $image Image reference
     * @return array Active converters
     */
    protected function getActiveConverters(FileInterface $image)
    {
        $skipConverter = array_filter(
            is_array($this->arguments['skipConverter']) ?
                $this->arguments['skipConverter'] :
                GeneralUtility::trimExplode(',', $this->arguments['skipConverter'], true)
        );
        $activeConverters = [];
        $availableConverters = $this->getResponsiveImagesUtility()->getAvailableConverters($image, $skipConverter);
        foreach (array_keys($availableConverters) as $converter) {
            $activeConverters[$converter] = $this->getImageSettings('converters.'.$converter);
        }
        return $activeConverters;
    }

    /**
     * Returns an instance of the responsive images utility
     * This fixes an issue with DI after clearing the cache
     *
     * @return ResponsiveImagesUtility
     */
    protected function getResponsiveImagesUtility()
    {
        return $this->objectManager->get(ResponsiveImagesUtility::class);
    }

    /**
     * Returns TypoSript settings array for images
     *
     * @param string $key Sub key
     * @return array Image settings
     */
    protected function getImageSettings($key = 'images')
    {
        /** @var ImageService $imageService */
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        return $imageService->getImageSettings($key);
    }

    /**
     * Determine the breakpoint specifications or preset to use
     *
     * @param string|array $breakpoints Breakpoint specifications or preset
     * @return array|string Breakpoint specifications
     */
    protected function getBreakpointSpecifications($breakpoints)
    {
        if (!is_array($breakpoints)) {
            $breakpoints = trim($breakpoints);
            $setup = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            list(, $configsPresets) = GeneralUtility::makeInstance(TypoScriptParser::class)
                ->getVal('lib.contentElement.settings.media.breakpoints', $setup);
            if (!empty($configsPresets['configs.'])
                && !empty($configsPresets['presets.'])
                && array_key_exists($breakpoints, $configsPresets['presets.'])
            ) {
                $configs = GeneralUtility::trimExplode(',', $configsPresets['presets.'][$breakpoints]);
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
     * @param string $width Image width
     * @param string $height Image height
     * @param array $breakpoints Breakpoint specifications
     * @param array $converters File converters
     * @return string Rendered <picture> element
     */
    protected function renderPicture(FileInterface $image, $width, $height, array $breakpoints, array $converters)
    {
        // Get crop variants
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);

        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $focusArea = $cropVariantCollection->getFocusArea($cropVariant);

        // Generate fallback image
        $fallbackImage = $this->generateFallbackImage($image, $width, $cropArea);

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
     * Generates a fallback image for picture and srcset markup
     *
     * @param FileInterface $image Original image
     * @param string $width Width
     * @param Area $cropArea Crop area
     * @return FileInterface Fallback image
     */
    protected function generateFallbackImage(FileInterface $image, $width, Area $cropArea)
    {
        return $this->getImageService()->applyProcessingInstructions($image, [
            'width' => $width,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ]);
    }

    /**
     * Render <img> element with srcset/sizes attributes
     *
     * @param  FileInterface $image Image reference
     * @param  string $width Image width
     * @param  string $height Image height
     * @return string Rendered <img> element
     */
    protected function renderImageSrcset(FileInterface $image, $width, $height)
    {
        // Get crop variants
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);

        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $focusArea = $cropVariantCollection->getFocusArea($cropVariant);

        // Generate fallback image
        $fallbackImage = $this->generateFallbackImage($image, $width, $cropArea);

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
     * @param  FileInterface $image Image reference
     * @param  string $width Image width
     * @param  string $height Image height
     * @return string Rendered <img> element
     */
    protected function renderLazyloadImage(FileInterface $image, $width, $height)
    {
        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width' => $width,
            'height' => $height,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        $imageService = $this->getImageService();
        $processedImage = $imageService->applyProcessingInstructions($image, $processingInstructions);
        $imageUri = $imageService->getImageUri($processedImage);

        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
            }
        }

        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));

        $alt = $image->getProperty('alternative');
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
     * Render an inline SVG graphic
     *
     * @param FileInterface $processedFile Processed file
     * @return string Inline SVG
     */
    protected function renderInlineSvg(FileInterface $processedFile)
    {
        $svgDom = new \DOMDocument();
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
