<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use Tollwerk\TwBase\Service\AbstractFileCompressorService;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Extended local CropScaleMask helper
 */
class LocalCropScaleMaskCompressHelper extends LocalCropScaleMaskHelper
{
    /**
     * This method actually does the processing of files locally
     *
     * Takes the original file (for remote storages this will be fetched from the remote server),
     * does the IM magic on the local server by creating a temporary typo3temp/ file,
     * copies the typo3temp/ file to the processing folder of the target storage and
     * removes the typo3temp/ file.
     *
     * The returned array has the following structure:
     *   width => 100
     *   height => 200
     *   filePath => /some/path
     *
     * If filePath isn't set but width and height are the original file is used as ProcessedFile
     * with the returned width and height. This is for example useful for SVG images.
     *
     * @param TaskInterface $task
     * @return array|NULL
     */
    public function process(TaskInterface $task)
    {
        $result = parent::process($task);
        if ($result !== null) {
            $fileExtension = strtolower($task->getSourceFile()->getExtension());
            $fileCompressor = GeneralUtility::makeInstanceService('filecompress', $fileExtension);
            if ($fileCompressor instanceof AbstractFileCompressorService) {
                $compressorSettings = $this->getImageSettings()['compress'];
                $fileFormatSettings = empty($compressorSettings[$fileExtension]) ? [] : $compressorSettings[$fileExtension];
                $result['filePath'] = $fileCompressor->processFile($task, $result, $fileFormatSettings);
            }
        }

        return $result;
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
        $setup = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'TwBase'
        );

        return $setup['images'];
    }
}
