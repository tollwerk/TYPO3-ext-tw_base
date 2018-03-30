<?php

namespace Tollwerk\TwBase\ViewHelpers\Media;

use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\ViewHelpers\MediaViewHelper;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renders the CSS for a responsive background image
 *
 * @see https://css-tricks.com/responsive-images-css/
 */
class CssViewHelper extends MediaViewHelper
{
    /**
     * Image service
     *
     * @var ImageService
     */
    protected $imageService = null;
    /**
     * Regular expression matching a simple media condition
     *
     * @var string
     */
//    const PCRE_MEDIA_COND = '/\((min|max)\-width\s*\:\s*(\d+(?:\.\d+)?)(vw|%|px)\)/';
    const PCRE_MEDIA_COND = '\((min|max)\-width\s*\:\s*(\d+(?:\.\d+)?)(vw|%|px|em|rem)\)';
    /**
     * Regular expression matching simple size queries
     *
     * @var string
     */
//    const PCRE_SIZE = '/^(?:'.self::PCRE_MEDIA_COND.'(?:\s*and\s*'.self::PCRE_MEDIA_COND.')*\s+)?(\d+(?:\.\d+)?)(vw|%|px)$/';
    const PCRE_SIZE = '/^('.self::PCRE_MEDIA_COND.'(?:\s*and\s*'.self::PCRE_MEDIA_COND.')*\s+)?(\d+(?:\.\d+)?)(vw|%|px|em|rem)$/';

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('selector', 'string', 'CSS selector to address the container element(s)', true);
        $this->registerArgument(
            'densities',
            'string',
            'Device density descriptors for responsive image',
            false,
            '1x'
        );
    }

    /**
     * Render an image element
     *
     * @param FileInterface $image Image reference
     * @param string $width        Image width
     * @param string $height       Image height
     *
     * @return string Rendered <img> or <picture> element
     */
    protected function renderImage(FileInterface $image, $width, $height)
    {
        // If the image shouldn't be inlined
        if (!$this->arguments['inline']) {
            $activeConverters = $this->getActiveConverters($image);

            // If the image should be rendered responsively (either because there are breakpoints / several sizes
            // configured or because there are active converters that should be applied)
            if ($this->arguments['responsive'] || count($activeConverters)) {
                // Determine the breakpoint specifications or preset to use
                $breakpoints = $this->getBreakpointSpecifications($this->arguments['breakpoints']);

//                // If there are breakpoint specifications available: Render as <picture> element
//                if (!empty($breakpoints) || count($activeConverters)) {
//                    return $this->renderPicture($image, $width, $height, $breakpoints, $activeConverters);
//
//                    // If a source set can be used: Render with srcset attribute
//                } else
                if ($this->arguments['srcset'] && $this->getResponsiveImagesUtility()->canSrcset($image)) {
                    return $this->renderImageSrcset($image, $width, $height);
                }
            }

            // If the image should only be lazyloaded
//            if ($this->arguments['lazyload']) {
//                return $this->renderLazyloadImage($image, $width, $height);
//            }
        }

        // Fall back to a simple image
        return $this->renderSimpleImage($image, $width, $height);
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
    protected function renderSimpleImage(FileInterface $image, $width, $height)
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
        $processedImage         = $this->getImageService()
                                       ->applyProcessingInstructions($image, $processingInstructions);

        // If the image should be inlined: Create a data URI
        if ($this->arguments['inline']) {
            $imageUri = $this->getResponsiveImagesUtility()->getDataUri(
                $processedImage->getMimeType(),
                $processedImage->getForLocalProcessing()
            );
        } else {
            $imageUri = $this->getImageService()->getImageUri($processedImage);
        }

        return $this->wrapCssRule('background-image: url("'.$imageUri.'")');
    }


    /**
     * Render <img> element with srcset/sizes attributes
     *
     * @param  FileInterface $image Image reference
     * @param  string $width        Image width
     * @param  string $height       Image height
     *
     * @return string Rendered <img> element
     */
    protected function renderImageSrcset(FileInterface $image, $width, $height)
    {
        $css = [];

        // Get crop variants & generate fallback image
        list($cropArea, $focusArea, $fallbackImage) = $this->createAreasAndFallback($image, $width);

        // Generate fallback image url
        $fallbackImageUri = $this->getImageService()->getImageUri($fallbackImage);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        // Generate different image sizes for srcset attribute
        $srcsetImages                           = $this->getResponsiveImagesUtility()
                                                       ->generateSrcsetImages(
                                                           $image,
                                                           $referenceWidth,
                                                           $this->arguments['srcset'],
                                                           $cropArea,
                                                           false
                                                       );
        $srcsetMode                             = substr(key($srcsetImages), -1); // x or w
        $fallbackWidthDescriptor                = ($srcsetMode == 'x') ? '1x' : $referenceWidth.'w';
        $srcsetImages[$fallbackWidthDescriptor] = $fallbackImage;
        $css                                    = ($srcsetMode === 'x') ?
            $this->createPixelDensityCss($srcsetImages) :
            $this->createWidthCss($srcsetImages, $referenceWidth);

        return implode(PHP_EOL, $css);
    }

    /**
     * Create CSS based on pixel density descriptors
     *
     * @param ProcessedFile[] $srcsetImages Image candidates
     *
     * @return array CSS rules
     */
    protected function createPixelDensityCss(array $srcsetImages)
    {
        $css = [];

        uksort($srcsetImages, [$this, 'sortByPixelDensityDescriptor']);

        // If there's a default image (pixel density descriptor 1)
        if (isset($srcsetImages['1x'])) {
            $css[] = $this->wrapCssRule(
                'background-image: url("'.$this->getImageService()->getImageUri($srcsetImages['1x']).'")'
            );
            unset($srcsetImages['1x']);
        }

        // Run through all source set images
        foreach ($srcsetImages as $widthDescriptor => $imageCandidate) {
            $css[] = $this->wrapMediaQuery(
                $this->wrapCssRule(
                    'background-image: url("'.$this->getImageService()->getImageUri($imageCandidate).'")'
                ),
                $this->createPixelDensityMediaQuery(floatval(substr($widthDescriptor, 0, -1)))
            );
        }

        return $css;
    }

    /**
     * Create CSS based on width descriptors
     *
     * @param ProcessedFile[] $srcsetImages Image candidates
     * @param int $referenceWidth           Reference width
     *
     * @return array CSS rules
     */
    protected function createWidthCss(array $srcsetImages, $referenceWidth)
    {
        $css         = [];
        $sizeQueries = strtolower($this->arguments['sizes']);
        if (strpos($sizeQueries, '%1$d')) {
            $sizeQueries = sprintf($sizeQueries, $referenceWidth);
        }
        $sizeQueries       = GeneralUtility::trimExplode(',', $sizeQueries, true);
        $densities         = array_filter(
            array_map(
                [$this, 'parseDensityDescriptor'],
                GeneralUtility::trimExplode(',', $this->arguments['densities'], true)
            )
        );
        $densities         = count($densities) ? $densities : [1];
        $filesAtConditions = [];

        // Start with the default size (must not have a media feature condition)
        try {
            $defaultSizeQuery = array_pop($sizeQueries);
            list($defaultTarget, $defaultConditions) = $this->parseSizeQuery($defaultSizeQuery);
//            if (count($defaultConditions)) {
            if ($defaultConditions) {
                throw new \InvalidArgumentException(
                    sprintf('Default size query "%s" must not have a media feature condition', $defaultSizeQuery),
                    1522421765
                );
            }
            $this->createTargetCssForDensities(
                $defaultTarget,
                null,
                $srcsetImages,
                $densities,
                $filesAtConditions
            );

        } catch (\InvalidArgumentException $e) {
            return ['/* '.$e->getMessage().' */'];
        }

        // Continue with the remaining sizes (must have media feature conditions)
        foreach ($sizeQueries as $sizeQuery) {
            list($target, $conditions) = $this->parseSizeQuery($sizeQuery);
//            if (!count($conditions)) {
            if (!$conditions) {
                throw new \InvalidArgumentException(
                    sprintf('Size query "%s" must have a valid media feature condition', $sizeQuery),
                    1522425426
                );
            }

            $this->createTargetCssForDensities(
                $target,
                $conditions,
                $srcsetImages,
                $densities,
                $filesAtConditions
            );
        }

        print_r($filesAtConditions);

        return $css;
    }

    /**
     * Create the CSS rules for a particular target size and a list of densities
     *
     * @param array $targetProperties       Target properties
     * @param string $conditions            Media feature conditions
     * @param ProcessedFile[] $srcsetImages Image candidates
     * @param array $densities              Device densities
     * @param array $filesAtConditions      Files and media query condition associations
     */
    protected function createTargetCssForDensities(
        array $targetProperties,
        $conditions,
        array $srcsetImages,
        array $densities,
        array &$filesAtConditions
    ) {
        // If the target size is absolute
        if ($targetProperties['absolute']) {
            foreach ($densities as $density) {
                $this->findBestMatchingImageForAbsoluteSizeAndDensity(
                    $targetProperties['value'],
                    $conditions,
                    $density,
                    $srcsetImages,
                    $filesAtConditions
                );
            }
        } else {

        }
    }

    /**
     * Find the best matching file for an absolute size and device density
     *
     * @param int $targetSize               Pixel target size
     * @param string $conditions            Media feature conditions
     * @param float $density                Device density
     * @param ProcessedFile[] $srcsetImages Image candidates
     * @param array $filesAtConditions      Files and media query condition associations
     */
    protected function findBestMatchingImageForAbsoluteSizeAndDensity(
        $targetSize,
        $conditions,
        $density,
        array $srcsetImages,
        array &$filesAtConditions
    ) {
        $densityTargetSize = $targetSize * $density;

        // Run through all image candidates
        foreach ($srcsetImages as $srcsetImage) {
            if ($srcsetImage->getProperty('width') >= $densityTargetSize) {
                break;
            }
        }

        // Register the filename
        $imageFilename = $this->getImageService()->getImageUri($srcsetImage);
        if (!array_key_exists($imageFilename, $filesAtConditions)) {
            $filesAtConditions[$imageFilename] = [];
        }

        // Register the appropriate media query
        $filesAtConditions[$imageFilename] = array_merge(
            $filesAtConditions[$imageFilename],
            ($density == 1) ? ['screen'] : $this->createPixelDensityMediaQuery($density, array_filter([$conditions]))
        );
    }

    /**
     * Parse a single size query
     *
     * @param string $sizeQuery Size query
     *
     * @return array Size query target and conditions
     * @throws \InvalidArgumentException If the size query is invalid
     */
    protected function parseSizeQuery($sizeQuery)
    {
        // Match the size query
        if (!preg_match(self::PCRE_SIZE, trim($sizeQuery), $sizeQueryMatch)) {
            throw new \InvalidArgumentException(sprintf('Invalid size query "%s"', $sizeQuery), 1522419613);
        }

        // Determine the size conditions
        $conditions = [];
        if (strlen($sizeQueryMatch[1])) {
            preg_match_all('/'.self::PCRE_MEDIA_COND.'/', $sizeQueryMatch[1], $sizeConditionsMatch);
            foreach ($sizeConditionsMatch[1] as $index => $minMax) {
                $condition          = [
                    'type'     => ($minMax === 'min') ? 1 : -1,
                    'absolute' => $this->isAbsoluteUnit($sizeConditionsMatch[3][$index]),
                ];
                $condition['value'] = floatval($sizeConditionsMatch[2][$index]) / ($condition['absolute'] ? 1 : 100);
                $conditions[]       = $condition;
            }
        }

        // Determine the target properties
        $targetAbsolute = $this->isAbsoluteUnit($sizeQueryMatch[9]);
        $targetValue    = floatval($sizeQueryMatch[8]) / ($targetAbsolute ? 1 : 100);

//        print_r($sizeQueryMatch);

        return [
            // Target
            ['absolute' => $targetAbsolute, 'value' => $targetValue],
            trim($sizeQueryMatch[1]) ?: null,
            // Condition
            $conditions
        ];
    }

    /**
     * Parse a density descriptor and return it as floating point number
     *
     * @param string $density Density descriptor
     *
     * @return float|int Density factor
     */
    protected function parseDensityDescriptor($density)
    {
        return preg_match('/^(\d+(?:\.\d+)?)x$/i', $density, $densityMatch) ? floatval($densityMatch[1]) : 0;
    }

    /**
     * Return whether a CSS unit is absolute
     *
     * @param string $unit CSS unit
     *
     * @return bool Is absolute unit
     */
    protected function isAbsoluteUnit($unit)
    {
        return in_array($unit, ['px', 'em', 'rem']);
    }

    /**
     * Sort pixel density descriptors
     *
     * @param string $d1 Pixel density descriptor 1
     * @param string $d2 Pixel density descriptor 1
     *
     * @return int Sort order
     */
    protected function sortByPixelDensityDescriptor($d1, $d2)
    {
        $dn1 = floatval(substr($d1, 0, -1));
        $dn2 = floatval(substr($d2, 0, -1));

        return ($dn1 == $dn2) ? 0 : (($dn1 > $dn2) ? 1 : -1);
    }

    /**
     * Create a pixel density CSS media query
     *
     * @param float $density Pixel density
     *
     * @return string Media query
     */
    protected function createPixelDensityMediaQuery($density, array $additionalConditions = [])
    {
        $densityConditions = [
            '(-webkit-min-device-pixel-ratio: '.$density.')',
            '(min-resolution: '.round(91 * $density).'dpi)',
            '(min-resolution: '.$density.'dppx)',
        ];

        return array_map(function($densityCondition) use ($additionalConditions) {
            return implode(' and ', array_merge(['screen', $densityCondition], $additionalConditions));
        }, $densityConditions);
    }

    /**
     * Wrap a CSS properties into the given selector, making it a CSS rule
     *
     * @param sting $css CSS properties
     *
     * @return string CSS rule
     */
    protected function wrapCssRule($css)
    {
        return $this->arguments['selector']." { $css }";
    }

    /**
     * Wrap CSS rules into a media query
     *
     * @param string $css                CSS rules
     * @param string[]|string $condition Media query condition(s)
     *
     * @return string Media query
     */
    protected function wrapMediaQuery($css, $condition)
    {
        return '@media '.(is_array($condition) ? implode(', ', $condition) : $condition)." { $css }";
    }

    /**
     * Return an instance of ImageService
     *
     * @return ImageService
     */
    protected function getImageService()
    {
        if (!($this->imageService instanceof ImageService)) {
            $this->imageService = $this->objectManager->get(ImageService::class);
        }

        return $this->imageService;
    }
}
