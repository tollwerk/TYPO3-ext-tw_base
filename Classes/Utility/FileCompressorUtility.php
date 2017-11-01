<?php

/**
 * tollwerk
 *
 * @category Jkphl
 * @package Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Utility
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

namespace Tollwerk\TwBase\Utility;

use Tollwerk\TwBase\Service\AbstractFileCompressorService;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\Service\FileProcessingService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileCompressorUtility implements SingletonInterface
{
    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;
    /**
     * Pre-registered files
     *
     * @var array
     */
    protected static $registeredFiles = [];

    /**
     * Compress a freshly processed file
     *
     * @param FileProcessingService $fileProcessingService File processing service
     * @param DriverInterface $driver Storage driver
     * @param ProcessedFile $processedFile Processed file
     * @param FileInterface $originalFile Original file
     * @param $context Context
     * @param array $configuration Configuration
     */
    public function registerCompressFile(
        FileProcessingService $fileProcessingService,
        DriverInterface $driver,
        ProcessedFile $processedFile,
        FileInterface $originalFile,
        $context,
        array $configuration
    ) {
        // Register the file only if it needs processing
        if (!$processedFile->isProcessed()) {
            self::$registeredFiles[$processedFile->getUid()] = true;
        }
    }

    /**
     * Compress a freshly processed file
     *
     * @param FileProcessingService $fileProcessingService File processing service
     * @param DriverInterface $driver Storage driver
     * @param ProcessedFile $processedFile Processed file
     * @param FileInterface $originalFile Original file
     * @param $context Context
     * @param array $configuration Configuration
     */
    public function compressFile(
        FileProcessingService $fileProcessingService,
        DriverInterface $driver,
        ProcessedFile $processedFile,
        FileInterface $originalFile,
        $context,
        array $configuration
    ) {
        if (!empty(self::$registeredFiles[$processedFile->getUid()])) {
            $fileUri = $processedFile->getPublicUrl();
            $fileExtension = strtolower(pathinfo($fileUri, PATHINFO_EXTENSION));
            $fileCompressor = GeneralUtility::makeInstanceService('filecompress', $fileExtension);
            if ($fileCompressor instanceof AbstractFileCompressorService) {
                $compressorSettings = $this->getImageSettings()['compress'];
                $fileFormatSettings = empty($compressorSettings[$fileExtension]) ? [] : $compressorSettings[$fileExtension];
                $fileCompressor->compressFile($fileUri, $fileFormatSettings);
            }
        }
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
