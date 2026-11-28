<?php

/**
 * Renders a responsive image in modern formats
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers;

use Exception;
use Override;
use RuntimeException;
use Tollwerk\TwBase\Exception\ViewHelperException;
use TYPO3\CMS\Core\Html\Srcset\SrcsetAttribute;
use TYPO3\CMS\Core\Html\Srcset\SrcsetCandidate;
use TYPO3\CMS\Core\Html\Srcset\WidthSrcsetCandidate;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Renders a responsive image in modern formats
 *
 * A picture element with a source for each modern format and the original as image, each in the widths of the
 * descriptors. The alternative text comes from the metadata of the file unless given; an empty one marks a decorative
 * image. SVG images are vector graphics and render as they are.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class PictureViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Media types of the formats offered besides the original
     *
     * @var array<string, string>
     */
    private const array FORMATS = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
    ];

    /**
     * Tag of the image inside the picture
     *
     * @var string
     */
    #[Override]
    protected $tagName = 'img';

    /**
     * Constructor
     *
     * @param ImageService $imageService Processes images
     */
    public function __construct(private readonly ImageService $imageService)
    {
        parent::__construct();
    }

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('image', FileInterface::class . '|' . ExtbaseFileReference::class, 'Image', true);
        $this->registerArgument('srcset', 'string', 'Width descriptors', false, '320w, 640w, 960w, 1280w, 1920w');
        $this->registerArgument('sizes', 'string', 'Sizes the image is shown at', false, '100vw');
        $this->registerArgument('formats', 'string', 'Further formats, if TYPO3 processes them', false, 'avif, webp');
        $this->registerArgument('cropVariant', 'string', 'Crop variant', false, 'default');
        $this->registerArgument('alt', 'string', 'Alternative text, if it differs from the metadata', false);
        $this->registerArgument('loading', 'string', 'Loading of the image', false, 'lazy');
    }

    /**
     * Render the picture
     *
     * @return string Picture, or an empty string if the file is not available, as with f:image
     *
     * @throws ViewHelperException If the image is missing or the descriptors are invalid
     * @throws RuntimeException If the storage of the file cannot be found
     */
    public function render(): string
    {
        $image = $this->image();
        if ($this->isUnavailable($image)) {
            return '';
        }

        $this->tag->addAttribute('alt', $this->text($this->arguments['alt'] ?? $image->getProperty('alternative')));
        $this->tag->addAttribute('loading', $this->string('loading'));
        $this->tag->addAttribute('decoding', 'async');

        if ($image->getExtension() === 'svg') {
            $this->tag->addAttribute('src', $this->imageService->getImageUri($image));

            return '<picture>' . $this->tag->render() . '</picture>';
        }

        $crop = $this->cropArea($image);
        // Even if TYPO3 scales images up, no candidate is wider than the image or its area
        $maxWidth = (int) round($crop?->getWidth() ?? (float) $this->text($image->getProperty('width')));
        $sizes = $this->string('sizes');
        $sources = '';
        foreach (GeneralUtility::trimExplode(',', $this->string('formats'), true) as $format) {
            $type = self::FORMATS[strtolower($format)] ?? null;
            if (($type === null) || !GeneralUtility::inList($this->imageExtensions(), strtolower($format))) {
                continue;
            }

            $srcset = $this->srcset($image, $crop, $maxWidth, strtolower($format));
            if (!($srcset instanceof SrcsetAttribute)) {
                continue;
            }

            $sources .= \sprintf(
                '<source type="%s" srcset="%s" sizes="%s">',
                $type,
                htmlspecialchars($srcset->generateSrcset()),
                htmlspecialchars($sizes),
            );
        }

        $srcset = $this->srcset($image, $crop, $maxWidth, null) ?? throw new ViewHelperException(
            'The image cannot be processed',
            1795868381,
        );
        $widths = array_map(
            static fn(SrcsetCandidate $candidate): int => $candidate->getCalculatedWidth(),
            $srcset->getCandidates(),
        );
        $fallback = $this->imageService->applyProcessingInstructions($image, [
            ...(($widths === []) ? [] : ['width' => max($widths)]),
            ...(($maxWidth > 0) ? ['maxWidth' => $maxWidth] : []),
            'crop' => $crop,
        ]);
        $src = $this->imageService->getImageUri($fallback);
        if ($src === '') {
            // No public address, such as for a file in a storage that is not public
            return '';
        }

        $this->tag->addAttribute('src', $src);
        $this->tag->addAttribute('srcset', $srcset->generateSrcset());
        $this->tag->addAttribute('sizes', $sizes);
        $this->tag->addAttribute('width', $this->text($fallback->getProperty('width')));
        $this->tag->addAttribute('height', $this->text($fallback->getProperty('height')));

        return '<picture>' . $sources . $this->tag->render() . '</picture>';
    }

    /**
     * The image to render
     *
     * @return FileInterface
     *
     * @throws ViewHelperException If the image is missing
     */
    private function image(): FileInterface
    {
        $image = $this->arguments['image'] ?? null;
        if ($image instanceof ExtbaseFileReference) {
            $image = $image->getOriginalResource();
        }

        if (!$image instanceof FileInterface) {
            throw new ViewHelperException('The picture has no image', 1795547101);
        }

        return $image;
    }

    /**
     * Whether the file of an image is missing, deleted or in a storage that is offline
     *
     * @param FileInterface $image Image
     *
     * @return boolean
     *
     * @throws RuntimeException If the storage of the file cannot be found
     */
    private function isUnavailable(FileInterface $image): bool
    {
        $file = $image instanceof FileReference ? $image->getOriginalFile() : $image;

        return $file instanceof File && ($file->isMissing() || $file->isDeleted() || !$file->getStorage()->isOnline());
    }

    /**
     * Area of the image to show
     *
     * @param FileInterface $image Image
     *
     * @return Area|null Area, or NULL for the whole image
     */
    private function cropArea(FileInterface $image): ?Area
    {
        $crop = $image->hasProperty('crop') ? $image->getProperty('crop') : '';
        $variants = CropVariantCollection::create(\is_string($crop) ? $crop : '');
        $area = $variants->getCropArea($this->string('cropVariant'));

        return $area->isEmpty() ? null : $area->makeAbsoluteBasedOnFile($image);
    }

    /**
     * Sources of the image in one format
     *
     * @param FileInterface $image    Image
     * @param Area|null     $crop     Area of the image to show
     * @param integer       $maxWidth Width of the image or its area, 0 if unknown
     * @param string|null   $format   File extension of the format, or NULL for the original
     *
     * @return SrcsetAttribute|null Sources, or NULL if TYPO3 cannot write the format, so that it falls back to the
     *                              original or to another format
     *
     * @throws ViewHelperException If the descriptors are invalid
     */
    private function srcset(FileInterface $image, ?Area $crop, int $maxWidth, ?string $format): ?SrcsetAttribute
    {
        try {
            $srcset = SrcsetAttribute::createFromDescriptors(
                GeneralUtility::trimExplode(',', $this->string('srcset'), true),
            );
        } catch (Exception $exception) {
            throw new ViewHelperException(
                'The descriptors are invalid: ' . $exception->getMessage(),
                1795547102,
                $exception,
            );
        }

        foreach ($srcset->getCandidates() as $candidate) {
            $processed = $this->imageService->applyProcessingInstructions($image, [
                'width' => $candidate->getCalculatedWidth(),
                ...(($maxWidth > 0) ? ['maxWidth' => $maxWidth] : []),
                'crop' => $crop,
                ...(($format === null) ? [] : ['fileExtension' => $format]),
            ]);
            $written = $processed->getExtension() === $format && !$processed->usesOriginalFile();
            if ($format !== null && !$written) {
                return null;
            }

            $width = $processed->getProperty('width');
            // A candidate wider than the image or its area is as wide as it
            $narrower = \is_int($width) && ($width !== $candidate->getCalculatedWidth());
            if ($narrower && $candidate instanceof WidthSrcsetCandidate) {
                $candidate->setWidth($width);
            }

            $candidate->setUri($this->imageService->getImageUri($processed));
        }

        return $srcset;
    }

    /**
     * File extensions TYPO3 processes as images
     *
     * @return string Comma-separated list
     */
    private function imageExtensions(): string
    {
        $configuration = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
        $graphics = \is_array($configuration) ? ($configuration['GFX'] ?? null) : null;

        return \is_array($graphics) ? $this->text($graphics['imagefile_ext'] ?? '') : '';
    }

    /**
     * Value as text
     *
     * @param mixed $value Value
     *
     * @return string Text, or an empty string if the value is not scalar
     */
    private function text(mixed $value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }

    /**
     * String argument
     *
     * @param string $name Name of the argument
     *
     * @return string
     */
    private function string(string $name): string
    {
        return $this->text($this->arguments[$name] ?? '');
    }
}
