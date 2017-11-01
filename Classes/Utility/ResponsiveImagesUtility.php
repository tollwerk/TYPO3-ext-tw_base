<?php

namespace Tollwerk\TwBase\Utility;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;

class ResponsiveImagesUtility implements SingletonInterface
{
    /**
     * Object Manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * Image Service
     *
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     * @inject
     */
    protected $imageService;

    /**
     * Default media breakpoint configuration
     *
     * @var array
     */
    protected $breakpointPrototype = [
        'cropVariant' => 'default',
        'media' => '',
        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
        'srcset' => []
    ];

    /**
     * Creates an image tag with the provided srcset candidates
     *
     * @param FileInterface $originalImage
     * @param FileInterface $fallbackImage
     * @param array|string $srcset
     * @param Area $cropArea
     * @param Area $focusArea
     * @param string $sizesQuery
     * @param TagBuilder $tag
     * @param bool $picturefillMarkup
     * @param bool $absoluteUri
     *
     * @return TagBuilder
     */
    public function createImageTagWithSrcset(
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        $srcset,
        Area $cropArea = null,
        Area $focusArea = null,
        string $sizesQuery = null,
        TagBuilder $tag = null,
        bool $picturefillMarkup = true,
        bool $absoluteUri = false
    ): TagBuilder {
        $tag = $tag ?: $this->objectManager->get(TagBuilder::class, 'img');

        // Generate fallback image url
        $fallbackImageUri = $this->imageService->getImageUri($fallbackImage, $absoluteUri);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        if (!$picturefillMarkup) {
            $tag->addAttribute('src', $fallbackImageUri);
        }

        // Generate different image sizes for srcset attribute
        $srcsetImages = $this->generateSrcsetImages($originalImage, $referenceWidth, $srcset, $cropArea, $absoluteUri);
        $srcsetMode = substr(key($srcsetImages), -1); // x or w

        // Add fallback image to source options
        $fallbackWidthDescriptor = ($srcsetMode == 'x') ? '1x' : $referenceWidth.'w';
        $srcsetImages[$fallbackWidthDescriptor] = $fallbackImageUri;

        // Set srcset attribute for image tag
        $tag->addAttribute('srcset', $this->generateSrcsetAttribute($srcsetImages));

        // Add sizes attribute to image tag
        if ($srcsetMode == 'w' && $sizesQuery) {
            $tag->addAttribute('sizes', sprintf($sizesQuery, $referenceWidth));
        }

        // Provide image dimensions to be consistent with TYPO3 core behavior
        $tag->addAttribute('width', $referenceWidth);
        $tag->addAttribute('height', $fallbackImage->getProperty('height'));

        // Add metadata to image tag
        $this->addMetadataToImageTag($tag, $originalImage, $fallbackImage, $focusArea);

        return $tag;
    }

    /**
     * Creates a picture tag with the provided image breakpoints
     *
     * @param  FileInterface $originalImage
     * @param  FileInterface $fallbackImage
     * @param  array $breakpoints
     * @param  CropVariantCollection $cropVariantCollection
     * @param  Area $focusArea
     * @param  TagBuilder $tag
     * @param  TagBuilder $fallbackTag
     * @param  bool $picturefillMarkup
     * @param  bool $absoluteUri
     *
     * @return TagBuilder
     */
    public function createPictureTag(
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        array $breakpoints,
        CropVariantCollection $cropVariantCollection,
        Area $focusArea = null,
        TagBuilder $tag = null,
        TagBuilder $fallbackTag = null,
        bool $picturefillMarkup = true,
        bool $absoluteUri = false
    ): TagBuilder {
        $tag = $tag ?: $this->objectManager->get(TagBuilder::class, 'picture');
        $fallbackTag = $fallbackTag ?: $this->objectManager->get(TagBuilder::class, 'img');

        // Normalize breakpoint configuration
        $breakpoints = $this->normalizeImageBreakpoints($breakpoints);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        // Use last breakpoint as fallback image if it doesn't define a media query
        $lastBreakpoint = array_pop($breakpoints);
        if ($lastBreakpoint && !$lastBreakpoint['media'] && $picturefillMarkup) {
            // Generate different image sizes for last breakpoint
            $cropArea = $cropVariantCollection->getCropArea($lastBreakpoint['cropVariant']);
            $srcset = $this->generateSrcsetImages(
                $originalImage,
                $referenceWidth,
                $lastBreakpoint['srcset'],
                $cropArea,
                $absoluteUri
            );
            $srcsetMode = substr(key($srcset), -1); // x or w

            // Set srcset attribute for fallback image
            $fallbackTag->addAttribute('srcset', $this->generateSrcsetAttribute($srcset));

            // Set sizes query for fallback image
            if ($srcsetMode == 'w' && $lastBreakpoint['sizes']) {
                $fallbackTag->addAttribute('sizes', sprintf($lastBreakpoint['sizes'], $referenceWidth));
            }
        } else {
            // Breakpoint can't be used as fallback
            if ($lastBreakpoint) {
                array_push($breakpoints, $lastBreakpoint);
            }

            // Set srcset attribute for fallback image (not src as advised by picturefill)
            $fallbackImageUri = $this->imageService->getImageUri($fallbackImage, $absoluteUri);
            if ($picturefillMarkup) {
                $fallbackTag->addAttribute('srcset', $fallbackImageUri);
            } else {
                $fallbackTag->addAttribute('src', $fallbackImageUri);
            }
        }

        // Provide image width to be consistent with TYPO3 core behavior
        $fallbackTag->addAttribute('width', $referenceWidth);

        // Add metadata to fallback image
        $this->addMetadataToImageTag($fallbackTag, $originalImage, $fallbackImage, $focusArea);

        // Generate source tags for image breakpoints
        $sourceTags = [];
        foreach ($breakpoints as $breakpoint) {
            $cropArea = $cropVariantCollection->getCropArea($breakpoint['cropVariant']);
            $sourceTag = $this->createPictureSourceTag(
                $originalImage,
                $referenceWidth,
                $breakpoint['srcset'],
                $breakpoint['media'],
                $breakpoint['sizes'],
                $cropArea,
                $absoluteUri
            );
            $sourceTags[] = $sourceTag->render();
        }

        // Fill picture tag
        $tag->setContent(
            implode('', $sourceTags).$fallbackTag->render()
        );

        return $tag;
    }

    /**
     * Creates a source tag that can be used inside of a picture tag
     *
     * @param  FileInterface $originalImage
     * @param  int $defaultWidth
     * @param  array|string $srcset
     * @param  string $mediaQuery
     * @param  string $sizesQuery
     * @param  Area $cropArea
     * @param  bool $absoluteUri
     *
     * @return TagBuilder
     */
    public function createPictureSourceTag(
        FileInterface $originalImage,
        int $defaultWidth,
        $srcset,
        string $mediaQuery = '',
        string $sizesQuery = '',
        Area $cropArea = null,
        bool $absoluteUri = false
    ): TagBuilder {
        $cropArea = $cropArea ?: Area::createEmpty();

        // Generate different image sizes for srcset attribute
        $srcsetImages = $this->generateSrcsetImages($originalImage, $defaultWidth, $srcset, $cropArea, $absoluteUri);
        $srcsetMode = substr(key($srcsetImages), -1); // x or w

        // Create source tag for this breakpoint
        $sourceTag = $this->objectManager->get(TagBuilder::class, 'source');
        $sourceTag->addAttribute('srcset', $this->generateSrcsetAttribute($srcsetImages));
        if ($mediaQuery) {
            $sourceTag->addAttribute('media', $mediaQuery);
        }
        if ($srcsetMode == 'w' && $sizesQuery) {
            $sourceTag->addAttribute('sizes', sprintf($sizesQuery, $defaultWidth));
        }

        return $sourceTag;
    }

    /**
     * Adds metadata to image tag
     *
     * @param TagBuilder $tag
     * @param FileInterface $originalImage
     * @param FileInterface $fallbackImage
     * @param Area $focusArea
     *
     * @return void
     */
    public function addMetadataToImageTag(
        TagBuilder $tag,
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        Area $focusArea = null
    ) {
        $focusArea = $focusArea ?: Area::createEmpty();

        // Add focus area to image tag
        if (!$tag->hasAttribute('data-focus-area') && !$focusArea->isEmpty()) {
            $tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($fallbackImage));
        }

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        $alt = $originalImage->getProperty('alternative');
        if (!$tag->getAttribute('alt')) {
            $tag->addAttribute('alt', $alt);
        }
        $title = $originalImage->getProperty('title');
        if (!$tag->getAttribute('title') && $title) {
            $tag->addAttribute('title', $title);
        }
    }

    /**
     * Renders different image sizes for use in a srcset attribute
     *
     * Input:
     *   1: $srcset = [200, 400]
     *   2: $srcset = ['200w', '400w']
     *   3: $srcset = ['1x', '2x']
     *   4: $srcset = '200, 400'
     *
     * Output:
     *   1+2+4: ['200w' => 'path/to/image@200w.jpg', '400w' => 'path/to/image@200w.jpg']
     *   3: ['1x' => 'path/to/image@1x.jpg', '2x' => 'path/to/image@2x.jpg']
     *
     * @param  FileInterface $image
     * @param  int $defaultWidth
     * @param  array|string $srcset
     * @param  Area $cropArea
     * @param  bool $absoluteUri
     *
     * @return array
     */
    public function generateSrcsetImages(
        FileInterface $image,
        int $defaultWidth,
        $srcset,
        Area $cropArea = null,
        bool $absoluteUri = false
    ): array {
        $cropArea = $cropArea ?: Area::createEmpty();

        // Convert srcset input to array
        if (!is_array($srcset)) {
            $srcset = GeneralUtility::trimExplode(',', $srcset);
        }

        $images = [];
        foreach ($srcset as $widthDescriptor) {
            // Determine image width
            switch (substr($widthDescriptor, -1)) {
                case 'x':
                    $candidateWidth = (int)($defaultWidth * (float)substr($widthDescriptor, 0, -1));
                    break;

                case 'w':
                    $candidateWidth = (int)substr($widthDescriptor, 0, -1);
                    break;

                default:
                    $candidateWidth = (int)$widthDescriptor;
                    $widthDescriptor = $candidateWidth.'w';
            }

            // Generate image
            $processingInstructions = [
                'width' => $candidateWidth,
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];
            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
            $images[$widthDescriptor] = $this->imageService->getImageUri($processedImage, $absoluteUri);
        }

        return $images;
    }

    /**
     * Generates the content for a srcset attribute from an array of image urls
     *
     * Input:
     * [
     *   '200w' => 'path/to/image@200w.jpg',
     *   '400w' => 'path/to/image@400w.jpg'
     * ]
     *
     * Output:
     * 'path/to/image@200w.jpg 200w, path/to/image@400w.jpg 400w'
     *
     * @param  array $srcsetImages
     *
     * @return string
     */
    public function generateSrcsetAttribute(array $srcsetImages): string
    {
        $srcsetString = [];
        foreach ($srcsetImages as $widthDescriptor => $imageCandidate) {
            $srcsetString[] = $imageCandidate.' '.$widthDescriptor;
        }
        return implode(', ', $srcsetString);
    }

    /**
     * Normalizes the provided breakpoints configuration
     *
     * @param  array $breakpoints
     *
     * @return array
     */
    public function normalizeImageBreakpoints(array $breakpoints): array
    {
        foreach ($breakpoints as &$breakpoint) {
            $breakpoint = array_replace($this->breakpointPrototype, $breakpoint);
        }
        ksort($breakpoints);

        return $breakpoints;
    }
}
