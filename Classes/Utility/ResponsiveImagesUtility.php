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

use Tollwerk\TwBase\Service\AbstractImageFileConverterService;
use Tollwerk\TwBase\Service\AbstractLqipService;
use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\ViewHelpers\TagSequenceBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Responsive Images Utility
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class ResponsiveImagesUtility implements SingletonInterface
{
    /**
     * Image file extensions eligible for srcset processing
     *
     * @var string[]
     */
    const SRCSET_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    /**
     * Image file extensions eligible for picture processing
     *
     * @var string[]
     */
    const PICTURE_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * Image Service
     *
     * @var ImageService
     */
    protected $imageService;
    /**
     * Default media breakpoint configuration
     *
     * @var array
     */
    protected $breakpointPrototype = [
        'cropVariant' => 'default',
        'media'       => '',
        'sizes'       => '(min-width: %1$dpx) %1$dpx, 100vw',
        'srcset'      => []
    ];
    /**
     * List of all available image converters
     *
     * @var array
     */
    protected $availableImageConverters = null;

    /**
     * Inject the object manager
     *
     * @param ObjectManager $objectManager Object manager
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Inject the image service
     *
     * @param ImageService $imageService Image service
     */
    public function injectImageService(ImageService $imageService): void
    {
        $this->imageService = $imageService;
    }

    /**
     * Creates an image tag with the provided srcset candidates
     *
     * @param FileInterface $originalImage Original image
     * @param FileInterface $fallbackImage Fallback Image
     * @param array|string $srcset         Srcset candidates
     * @param Area $cropArea               Crop area
     * @param Area $focusArea              Focus area
     * @param string $sizesQuery           Sizes query
     * @param TagBuilder $tag              Tag builder
     * @param bool $picturefillMarkup      Add picturefill markup
     * @param array $lazyloadSettings      Lazyload settings
     * @param bool $absoluteUri            Create absolute URI
     *
     * @return TagBuilder Image tag
     * @throws Exception
     */
    public function createImageTagWithSrcset(
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        $srcset,
        Area $cropArea = null,
        Area $focusArea = null,
        ?string $sizesQuery = null,
        TagBuilder $tag = null,
        bool $picturefillMarkup = true,
        array $lazyloadSettings = null,
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
        $srcsetImages = $this->generateSrcsetImages($originalImage, $referenceWidth, $srcset, $cropArea);
        $srcsetMode   = substr(key($srcsetImages), -1); // x or w

        // Add fallback image to source options
        $fallbackWidthDescriptor                = ($srcsetMode == 'x') ? '1x' : $referenceWidth.'w';
        $srcsetImages[$fallbackWidthDescriptor] = $fallbackImage;

        // Add sizes attribute to image tag
        if (($srcsetMode == 'w') && $sizesQuery) {
            $tag->addAttribute('sizes', sprintf($sizesQuery, $referenceWidth));
        }

        // Provide image dimensions to be consistent with TYPO3 core behavior
        $tag->addAttribute('width', $referenceWidth);
        $tag->addAttribute('height', $fallbackImage->getProperty('height'));

        // Add metadata to image tag
        $this->addMetadataToImageTag($tag, $originalImage, $fallbackImage, $focusArea);

        // If this is a lazyloading image
        if (!empty($lazyloadSettings)) {
            $tag = $this->addLazyloadingToImageTag(
                $tag,
                $fallbackImageUri,
                $lazyloadSettings,
                $this->generateSrcsetAttribute($srcsetImages, $absoluteUri)
            );

            // Else: Set srcset attribute for image tag
        } else {
            $tag->addAttribute('srcset', $this->generateSrcsetAttribute($srcsetImages, $absoluteUri));
        }

        return $tag;
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
     * @param FileInterface $image Source image
     * @param int $defaultWidth    Default width
     * @param array|string $srcset Srcset candidates
     * @param Area $cropArea       Crop area
     *
     * @return ProcessedFile[] Srcset images
     * @throws Exception
     */
    public function generateSrcsetImages(
        FileInterface $image,
        int $defaultWidth,
        $srcset,
        Area $cropArea = null
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
                    $candidateWidth  = (int)$widthDescriptor;
                    $widthDescriptor = $candidateWidth.'w';
            }

            // Generate the image
            $images[$widthDescriptor] = $this->imageService->applyProcessingInstructions(
                $image,
                [
                    'width' => $candidateWidth,
                    'crop'  => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
                ]
            );
        }

        return $images;
    }

    /**
     * Adds metadata to an image tag
     *
     * @param TagBuilder $tag              Image tag
     * @param FileInterface $originalImage Original image
     * @param FileInterface $fallbackImage Fallback image
     * @param Area $focusArea              Focus area
     *
     * @return TagBuilder Image tag
     */
    public function addMetadataToImageTag(
        TagBuilder $tag,
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        Area $focusArea = null
    ): TagBuilder {
        $focusArea = $focusArea ?: Area::createEmpty();

        // Add focus area to image tag
        if (!$tag->hasAttribute('data-focus-area') && !$focusArea->isEmpty()) {
            $tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($fallbackImage));
        }

        // Add alt and title attributes
        $alt = $originalImage->getProperty('alternative');
        if (!$tag->getAttribute('alt')) {
            $tag->addAttribute('alt', $alt);
        }
        $title = $originalImage->getProperty('title');
        if (!$tag->getAttribute('title') && strlen($title)) {
            $tag->addAttribute('title', $title);
        }

        return $tag;
    }

    /**
     * Adds lazyloading data to an image tag
     *
     * @param TagBuilder $tag      Image tag
     * @param string $imageUri     Image URI
     * @param array $imageSettings Image settings
     * @param string $srcset       Srcset (responsive image)
     *
     * @return TagBuilder Lazyloading image tag
     */
    public function addLazyloadingToImageTag(
        TagBuilder $tag,
        string $imageUri,
        array $imageSettings,
        string $srcset = null
    ): TagBuilder {
        $lqipService = GeneralUtility::makeInstanceService('lqip', strtolower(pathinfo($imageUri, PATHINFO_EXTENSION)));
        $lqipUri     = ($lqipService instanceof AbstractLqipService) ?
            $lqipService->getImageLqip($imageUri, $imageSettings['lqip']) : false;

        if ($lqipUri) {
            $fallbackTag = clone $tag;
            $fallbackTag->addAttribute('src', $imageUri);

            if (strlen($srcset)) {
                $tag->addAttribute('data-srcset', $srcset);
                $fallbackTag->removeAttribute('sizes');
            } else {
                $tag->addAttribute('data-src', $imageUri);
            }
            $tag->addAttribute('src',
                $this->getDataUri('image/svg+xml', Environment::getPublicPath().'/'.$lqipUri));
            $tag->addAttribute('loading', 'lazy');

            $tag = new TagSequenceBuilder([$tag, new TagBuilder('noscript', $fallbackTag->render())]);
        } elseif (strlen($srcset)) {
            $tag->addAttribute('srcset', $srcset);
        } else {
            $tag->addAttribute('src', $imageUri);
        }

        return $tag;
    }

    /**
     * Return a base64 encoded data URI for a file
     *
     * @param string $mimeType MIME type
     * @param string $path     File path
     *
     * @return string Data URI
     */
    public function getDataUri($mimeType, $path): string
    {
        return 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($path));
    }

    /**
     * Generates the content for a srcset attribute from an array of image URLs
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
     * @param ProcessedFile[] $srcsetImages Srcset images
     * @param bool $absoluteUri             Create absolute URI
     *
     * @return string Srcset attribute
     */
    public function generateSrcsetAttribute(array $srcsetImages, $absoluteUri = false): string
    {
        $srcsetString = [];
        foreach ($srcsetImages as $widthDescriptor => $imageCandidate) {
            $srcsetString[] = $this->imageService->getImageUri($imageCandidate, $absoluteUri).' '.$widthDescriptor;
        }

        return implode(', ', $srcsetString);
    }

    /**
     * Creates a picture tag with the provided image breakpoints
     *
     * @param FileInterface $originalImage                 Original image
     * @param FileInterface $fallbackImage                 Fallback Image
     * @param array $breakpoints                           Breakpoints
     * @param CropVariantCollection $cropVariantCollection Crop variants
     * @param Area $focusArea                              Focus area
     * @param TagBuilder $tag                              Tag builder
     * @param TagBuilder $fallbackTag                      Fallback tag builder
     * @param bool $picturefillMarkup                      Add picturefill markup
     * @param array $lazyloadSettings                      Lazyload settings
     * @param array[] $converters                          File converters to apply
     * @param bool $absoluteUri                            Create absolute URI
     *
     * @return TagBuilder Picture tag
     * @throws Exception
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
        array $lazyloadSettings = null,
        array $converters = [],
        bool $absoluteUri = false
    ): TagBuilder {
        $tag         = $tag ?: $this->objectManager->get(TagBuilder::class, 'picture');
        $fallbackTag = $fallbackTag ?: $this->objectManager->get(TagBuilder::class, 'img');
        $this->moveAttributes($fallbackTag, $tag, ['id', 'class']);

        // Normalize breakpoint configuration
        $breakpoints = $this->normalizeImageBreakpoints($breakpoints);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        // Use last breakpoint as fallback image if it doesn't define a media query
        $lastBreakpoint = array_pop($breakpoints);
        if ($lastBreakpoint && !$lastBreakpoint['media'] && $picturefillMarkup) {

            // Generate different image sizes for last breakpoint
            $cropArea     = $cropVariantCollection->getCropArea($lastBreakpoint['cropVariant']);
            $srcsetImages = $this->generateSrcsetImages(
                $originalImage,
                $referenceWidth,
                $lastBreakpoint['srcset'],
                $cropArea
            );
            $srcsetMode   = substr(key($srcsetImages), -1); // x or w

            // Set sizes query for fallback image
            if ($srcsetMode == 'w' && $lastBreakpoint['sizes']) {
                $fallbackTag->addAttribute('sizes', sprintf($lastBreakpoint['sizes'], $referenceWidth));
            }

            // Else: Breakpoint can't be used as fallback, put it back on the stack
        } elseif ($lastBreakpoint) {
            array_push($breakpoints, $lastBreakpoint);
        }

        // Create and convert all source tags
        list($sourceTags, $convertedSourceTags) = $this->createAndConvertPictureSourceTags(
            $originalImage,
            $breakpoints,
            $cropVariantCollection,
            $referenceWidth,
            $lazyloadSettings,
            $converters,
            $absoluteUri
        );

        // Add converted fallback alternatives
        $convertedSourceTags = array_merge(
            $convertedSourceTags,
            $this->createConvertedFallbackAlternatives(
                $fallbackImage,
                $lazyloadSettings,
                $converters,
                $absoluteUri
            )
        );

        // Finalize the fallback tag
        $fallbackTag = $this->createPictureFallbackTag(
            $fallbackTag,
            $originalImage,
            $fallbackImage,
            $referenceWidth,
            $picturefillMarkup,
            $absoluteUri,
            $focusArea,
            $lazyloadSettings
        );

        // Fill picture tag
        $tag->setContent(implode('', $convertedSourceTags).implode('', $sourceTags).$fallbackTag->render());

        return $tag;
    }

    /**
     * Move attributes from one tag to anoterh
     *
     * @param TagBuilder $from     Source tag
     * @param TagBuilder $to       Target tag
     * @param string[] $attributes Attribute names to move
     */
    protected function moveAttributes(TagBuilder $from, TagBuilder $to, array $attributes = []): void
    {
        foreach ($attributes as $attribute) {
            if ($from->hasAttribute($attribute)) {
                $to->addAttribute($attribute, $from->getAttribute($attribute));
                $from->removeAttribute($attribute);
            }
        }
    }

    /**
     * Normalizes the provided breakpoints specification
     *
     * @param array $breakpoints Breakpoint specification
     *
     * @return array Normalized breakpoint specification
     */
    public function normalizeImageBreakpoints(array $breakpoints): array
    {
        foreach ($breakpoints as &$breakpoint) {
            $breakpoint = array_replace($this->breakpointPrototype, $breakpoint);
        }
        ksort($breakpoints);

        return $breakpoints;
    }

    /**
     * Creates and convert the source tags for a picture element
     *
     * @param FileInterface $originalImage                 Original image
     * @param array $breakpoints                           Breakpoints
     * @param CropVariantCollection $cropVariantCollection Crop variants
     * @param int $defaultWidth                            Default width
     * @param array $lazyloadSettings                      Lazyload settings
     * @param array[] $converters                          File converters to apply
     * @param bool $absoluteUri                            Create absolute URI
     *
     * @return array[] Source tags (regular and converted)
     * @throws Exception
     */
    protected function createAndConvertPictureSourceTags(
        FileInterface $originalImage,
        array $breakpoints,
        CropVariantCollection $cropVariantCollection,
        int $defaultWidth,
        array $lazyloadSettings = null,
        array $converters = [],
        bool $absoluteUri = false
    ): array {
        // Generate source tags for image breakpoints
        $sourceTags = $convertedSourceTags = [];
        foreach ($breakpoints as $breakpoint) {
            $srcsetImages = [];
            $sourceTag    = $this->createPictureSourceTag(
                $originalImage,
                $defaultWidth,
                $breakpoint['srcset'],
                $breakpoint['media'],
                $breakpoint['sizes'],
                $cropVariantCollection->getCropArea($breakpoint['cropVariant']),
                $lazyloadSettings,
                $absoluteUri,
                $srcsetImages
            );
            $sourceTags[] = $sourceTag->render();

            // If images have been rendered: Pass them to all active converters and create additional source tags
            if (count($srcsetImages)) {
                // Run through all active converters
                foreach ($converters as $converterKey => $converterConfig) {
                    $convertedSrcsetImages = $this->convertImages($srcsetImages, $converterKey, $converterConfig);
                    if (count($convertedSrcsetImages)) {
                        $convertedSourceTag = clone $sourceTag;
                        $convertedSourceTag->addAttribute(
                            empty($lazyloadSettings) ? 'srcset' : 'data-srcset',
                            $this->generateSrcsetAttribute($convertedSrcsetImages, $absoluteUri)
                        );
                        $convertedSourceTag->addAttribute('type', current($convertedSrcsetImages)->getMimeType());
                        $convertedSourceTags[] = $convertedSourceTag->render();
                    }
                }
            }
        }

        return [$sourceTags, $convertedSourceTags];
    }

    /**
     * Creates a source tag that can be used inside of a picture tag
     *
     * @param FileInterface $originalImage Original image
     * @param int $defaultWidth            Default width
     * @param array|string $srcset         Srcset candidates
     * @param string $mediaQuery           Media query
     * @param string $sizesQuery           Sizes query
     * @param Area $cropArea               Crop area
     * @param array $lazyloadSettings      Lazyload settings
     * @param bool $absoluteUri            Create absolute URI
     * @param array $srcsetImages
     *
     * @return TagBuilder Source tag
     * @throws Exception
     */
    public function createPictureSourceTag(
        FileInterface $originalImage,
        int $defaultWidth,
        $srcset,
        string $mediaQuery = '',
        string $sizesQuery = '',
        Area $cropArea = null,
        array $lazyloadSettings = null,
        bool $absoluteUri = false,
        array &$srcsetImages = []
    ): TagBuilder {
        $cropArea = $cropArea ?: Area::createEmpty();

        // Generate different image sizes for srcset attribute
        $srcsetImages = $this->generateSrcsetImages($originalImage, $defaultWidth, $srcset, $cropArea);
        $srcsetMode   = substr(key($srcsetImages), -1); // x or w

        // Create source tag for this breakpoint
        $sourceTag = $this->objectManager->get(TagBuilder::class, 'source');
        $sourceTag->addAttribute(
            empty($lazyloadSettings) ? 'srcset' : 'data-srcset',
            $this->generateSrcsetAttribute($srcsetImages, $absoluteUri)
        );
        if ($mediaQuery) {
            $sourceTag->addAttribute('media', $mediaQuery);
        }
        if ($srcsetMode == 'w' && $sizesQuery) {
            $sourceTag->addAttribute('sizes', sprintf($sizesQuery, $defaultWidth));
        }

        return $sourceTag;
    }

    /**
     * Convert a list of images using a particular converter
     *
     * @param array|ProcessedFile $images Images
     * @param string $converterKey        Converter key
     * @param array $converterConfig      Converter configuration
     *
     * @return ProcessedFile[] Converted images
     */
    protected function convertImages(
        array $images,
        string $converterKey,
        array $converterConfig = []
    ): array {
        $imageService =& $this->imageService;

        return array_filter(
            array_map(
                function(ProcessedFile $image) use ($imageService, $converterKey, $converterConfig) {
                    $convertedImage = $imageService->convert($image, $converterKey, $converterConfig);

                    return $convertedImage->usesOriginalFile() ? null : $convertedImage;
                }, $images
            )
        );
    }

    /**
     * Create converted fallback image alternatives
     *
     * @param FileInterface $fallbackImage Fallback image
     * @param array $lazyloadSettings      Lazyload settings
     * @param array[] $converters          File converters to apply
     * @param bool $absoluteUri            Create absolute URI
     *
     * @return string[] Alternative fallback image tags
     * @throws Exception
     */
    public function createConvertedFallbackAlternatives(
        FileInterface $fallbackImage,
        array $lazyloadSettings = null,
        array $converters = [],
        bool $absoluteUri = false
    ): array {
        $fallbackAlternativeTags = [];
        foreach ($converters as $converterKey => $converterConfig) {
            /** @var ProcessedFile $processedImage */
            $processedImage = $this->imageService->convert($fallbackImage, $converterKey, $converterConfig);
            if (!$processedImage->usesOriginalFile()) {
                $convertedImageUri = $this->imageService->getImageUri($processedImage, $absoluteUri);

                // Create source tag for this breakpoint
                $sourceTag = $this->objectManager->get(TagBuilder::class, 'source');
                $sourceTag->addAttribute(empty($lazyloadSettings) ? 'srcset' : 'data-srcset', $convertedImageUri);
                $sourceTag->addAttribute('type', $processedImage->getMimeType());
                $fallbackAlternativeTags[] = $sourceTag->render();
            }
        }

        return $fallbackAlternativeTags;
    }

    /**
     * Create the fallback tag for a picture element
     *
     * @param TagBuilder|null $fallbackTag Fallback tag
     * @param FileInterface $originalImage Original image
     * @param FileInterface $fallbackImage Fallback image
     * @param int $referenceWidth          Reference width
     * @param bool $picturefillMarkup      Add picturefill markup
     * @param bool $absoluteUri            Create absolute URI
     * @param Area|null $focusArea         Focus area
     * @param array|null $lazyloadSettings Lazyload settings
     *
     * @return TagBuilder Fallback tag
     */
    public function createPictureFallbackTag(
        TagBuilder $fallbackTag,
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        int $referenceWidth,
        bool $picturefillMarkup,
        bool $absoluteUri,
        Area $focusArea = null,
        array $lazyloadSettings = null
    ): TagBuilder {
        // Get the fallback image URI
        $fallbackImageUri = $this->imageService->getImageUri($fallbackImage, $absoluteUri);
        $aspectRatio      = $fallbackImage->getProperty('width') / $fallbackImage->getProperty('height');

        // Provide image width to be consistent with TYPO3 core behavior
        $fallbackTag->addAttribute('width', $referenceWidth);
        $fallbackTag->addAttribute('height', round($referenceWidth / $aspectRatio));

        // Add metadata to fallback image
        $this->addMetadataToImageTag($fallbackTag, $originalImage, $fallbackImage, $focusArea);

        // If this is a lazyloading image: Prepare the fallback tag
        if (!empty($lazyloadSettings)) {
            $fallbackTag = $this->addLazyloadingToImageTag(
                $fallbackTag,
                $fallbackImageUri,
                $lazyloadSettings,
                $fallbackImageUri
            );

            // Else: If picturefill markup should be used
        } elseif ($picturefillMarkup) {
            $fallbackTag->addAttribute('srcset', $fallbackImageUri);
            $fallbackTag->addAttribute('src', $fallbackImageUri);

            // Else: Standard image
        } else {
            $fallbackTag->addAttribute('src', $fallbackImageUri);
        }

        return $fallbackTag;
    }

    /**
     * Test whether an image is eligible for srcset processing
     *
     * @param FileInterface $image Image
     *
     * @return bool Image can have srcset
     */
    public function canSrcset(FileInterface $image): bool
    {
        return in_array(strtolower($image->getExtension()), self::SRCSET_FILE_EXTENSIONS);
    }

    /**
     * Test whether an image is eligible for picture processing
     *
     * @param FileInterface $image Image
     *
     * @return bool Image can have srcset
     */
    public function canPicture(FileInterface $image): bool
    {
        return in_array(strtolower($image->getExtension()), self::PICTURE_FILE_EXTENSIONS);
    }

    /**
     * Return all available image converters
     *
     * @param FileInterface $image Image reference
     * @param array $skip          Skip converters
     *
     * @return array Available image converters
     */
    public function getAvailableConverters(FileInterface $image, array $skip = []): array
    {
        if ($this->availableImageConverters === null) {
            $this->availableImageConverters = [];

            // Test if the AVIF converter is available
            $avifConverterService = GeneralUtility::makeInstanceService('fileconvert', 'avif');
            if ($avifConverterService instanceof AbstractImageFileConverterService) {
                $this->availableImageConverters['avif'] = $avifConverterService;
            }

            // Test if the WebP converter is available
            $webPConverterService = GeneralUtility::makeInstanceService('fileconvert', 'webp');
            if ($webPConverterService instanceof AbstractImageFileConverterService) {
                $this->availableImageConverters['webp'] = $webPConverterService;
            }
        }

        $availableConverters = array_filter(
            $this->availableImageConverters,
            function(AbstractImageFileConverterService $converter) use ($image) {
                return $converter->acceptsFile($image);
            }
        );

        return array_diff_key($availableConverters, array_flip(array_filter($skip)));
    }
}
