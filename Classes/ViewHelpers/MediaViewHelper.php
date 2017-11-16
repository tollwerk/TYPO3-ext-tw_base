<?php

namespace Tollwerk\TwBase\ViewHelpers;

use Tollwerk\TwBase\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;

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
        $this->registerArgument('breakpoints', 'array', 'Image breakpoints from responsive design.', false);
        $this->registerArgument('picturefill', 'bool', 'Use rendering suggested by picturefill.js', false, true);
        $this->registerArgument('responsive', 'bool', 'Render responsive image', false, true);
        $this->registerArgument('lazyload', 'bool', 'Use lazyloading', false, false);
    }

    /**
     * Render <img> element
     *
     * @param  FileInterface $image Image reference
     * @param  string $width Image width
     * @param  string $height Image height
     * @return string Rendered <img> element
     */
    protected function renderImage(FileInterface $image, $width, $height)
    {
        // If the image should be rendered responsively
        if ($this->arguments['responsive']) {
            if ($this->arguments['breakpoints']) {
                return $this->renderPicture($image, $width, $height);
            } elseif ($this->arguments['srcset']) {
                return $this->renderImageSrcset($image, $width, $height);
            }
        }

        // If the image should be lazyloaded
        if ($this->arguments['lazyload']) {
            return $this->renderLazyloadImage($image, $width, $height);
        }

        // Return a regular image
        return parent::renderImage($image, $width, $height);
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
     * Render <picture> element
     *
     * @param  FileInterface $image Image reference
     * @param  string $width Image width
     * @param  string $height Image height
     * @return string Rendered <picture> element
     */
    protected function renderPicture(FileInterface $image, $width, $height)
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
            $this->arguments['breakpoints'],
            $cropVariantCollection,
            $focusArea,
            null,
            $this->tag,
            $this->arguments['picturefill']
        );

        return $this->tag->render();
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
     * Generates a fallback image for picture and srcset markup
     *
     * @param  FileInterface $image
     * @param  string $width
     * @param  Area $cropArea
     *
     * @return FileInterface
     */
    protected function generateFallbackImage(FileInterface $image, $width, Area $cropArea)
    {
        $processingInstructions = [
            'width' => $width,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        $imageService = $this->getImageService();
        $fallbackImage = $imageService->applyProcessingInstructions($image, $processingInstructions);

        return $fallbackImage;
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
     * @param string $extension Name of the extension
     * @param string $plugin Name of the plugin
     * @return array
     */
    protected function getImageSettings()
    {
        $typoScript = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'TwBase'
        );

        return $typoScript['images'];
    }
}
