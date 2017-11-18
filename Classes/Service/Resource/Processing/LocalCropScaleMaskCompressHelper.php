<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use Tollwerk\TwBase\Service\AbstractFileCompressorService;
use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\Utility\ArrayUtility;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extended local CropScaleMask helper
 */
class LocalCropScaleMaskCompressHelper extends LocalCropScaleMaskHelper
{
    /**
     * This method actually does the processing of files locally
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
                /** @var ImageService $imageService */
                $imageService = GeneralUtility::makeInstance(ImageService::class);
                $fileFormatSettings = ArrayUtility::recursivelyFalsify($imageService->getImageSettings('images.compress.'.$fileExtension));
                $result['filePath'] = $fileCompressor->processFile($task, $result, $fileFormatSettings);
            }
        }

        return $result;
    }
}
