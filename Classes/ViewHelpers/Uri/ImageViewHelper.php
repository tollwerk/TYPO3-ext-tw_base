<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Uri
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

namespace Tollwerk\TwBase\ViewHelpers\Uri;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Tollwerk\TwBase\Service\ImageService;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception as ExtbaseException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use UnexpectedValueException;

/**
 * Extended image view helper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers\Uri
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper
{
    /**
     * Resizes the image (if required) and returns its path. If the image was not resized, the path will be equal to
     * $src
     *
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Image path
     * @throws ExtbaseException
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $src                = $arguments['src'];
        $image              = $arguments['image'];
        $treatIdAsReference = $arguments['treatIdAsReference'];
        $cropString         = $arguments['crop'];
        $absolute           = $arguments['absolute'];

        if (($src === null && $image === null) || ($src !== null && $image !== null)) {
            throw new Exception('You must either specify a string src or a File object.', 1460976233);
        }

        try {
            $imageService = self::getImageService();
            $image        = $imageService->getImage($src, $image, $treatIdAsReference);

            if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
                $cropString = $image->getProperty('crop');
            }

            $cropVariantCollection  = CropVariantCollection::create((string)$cropString);
            $cropVariant            = $arguments['cropVariant'] ?: 'default';
            $cropArea               = $cropVariantCollection->getCropArea($cropVariant);
            $processingInstructions = [
                'width'     => $arguments['width'],
                'height'    => $arguments['height'],
                'minWidth'  => $arguments['minWidth'],
                'minHeight' => $arguments['minHeight'],
                'maxWidth'  => $arguments['maxWidth'],
                'maxHeight' => $arguments['maxHeight'],
                'crop'      => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];

            $processedImage = $imageService->applyProcessingInstructions($image, $processingInstructions);

            return $imageService->getImageUri($processedImage, $absolute);
        } catch (UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
            throw new Exception($e->getMessage(), 1509741908, $e);
        } catch (RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
            throw new Exception($e->getMessage(), 1509741909, $e);
        } catch (InvalidArgumentException $e) {
            // thrown if file storage does not exist
            throw new Exception($e->getMessage(), 1509741910, $e);
        }
    }

    /**
     * Return an instance of ImageService using object manager
     *
     * @return ImageService
     * @throws ExtbaseException
     */
    protected static function getImageService(): ImageService
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        return $objectManager->get(ImageService::class);
    }
}
