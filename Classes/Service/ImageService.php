<?php

/**
 * tollwerk
 *
 * @category Jkphl
 * @package Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Service
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

namespace Tollwerk\TwBase\Service;

use Tollwerk\TwBase\Utility\ArrayUtility;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Extended image service
 */
class ImageService extends \TYPO3\CMS\Extbase\Service\ImageService
{
    /**
     * Image settings
     *
     * @var array
     */
    protected $imageSettings = null;
    /**
     * Extended processing context for the frontend including file compression
     */
    const CONTEXT_IMAGECROPSCALEMASKCOMPRESS = 'Image.CropScaleMaskCompress';

    /**
     * Create a processed file
     *
     * @param FileInterface|FileReference $image
     * @param array $processingInstructions
     * @return ProcessedFile Processed file
     * @api
     */
    public function applyProcessingInstructions($image, $processingInstructions)
    {
        if (is_callable([$image, 'getOriginalFile'])) {
            // Get the original file from the file reference
            $image = $image->getOriginalFile();
        }

        // Enable file compression
        $processingInstructions['compress'] = $this->hasCompressorEnabled($image) ?
            ArrayUtility::recursivelyFalsify($this->getImageSettings('images.compress.'.$image->getExtension())) : false;

        // Process the image
        $processedImage = $image->process(self::CONTEXT_IMAGECROPSCALEMASKCOMPRESS, $processingInstructions);
        $this->setCompatibilityValues($processedImage);

        return $processedImage;
    }

    /**
     * Test whether there's an active compressor available
     *
     * @param FileInterface $file File
     * @return bool Compressor is available
     */
    protected function hasCompressorEnabled(FileInterface $file)
    {
        $fileExtension = strtolower($file->getExtension());
        $fileCompressor = GeneralUtility::makeInstanceService('filecompress', $fileExtension);
        return $fileCompressor instanceof AbstractFileCompressorService;
    }

    /**
     * Extract and return the image settings
     *
     * @return string $key Optional: Key
     * @return array Image settings
     */
    public function getImageSettings($key = null)
    {
        if ($this->imageSettings === null) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
            $this->imageSettings = $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'TwBase'
            );
        }

        if ($key === null) {
            return $this->imageSettings;
        }

        $imageSettings = $this->imageSettings;
        foreach (explode('.', $key) as $step) {
            if (!array_key_exists($step, $imageSettings)) {
                return null;
            }
            $imageSettings = $imageSettings[$step];
        }
        return $imageSettings;
    }
}
