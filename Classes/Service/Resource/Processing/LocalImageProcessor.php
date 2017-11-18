<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extended local image processor
 */
class LocalImageProcessor extends \TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor
{
    /**
     * Returns TRUE if this processor can process the given task.
     *
     * @param TaskInterface $task
     * @return bool
     */
    public function canProcessTask(TaskInterface $task)
    {
        $canProcessTask = $task->getType() === 'Image';
        $canProcessTask = $canProcessTask & in_array(
                $task->getName(),
                ['Preview', 'CropScaleMask', 'CropScaleMaskCompress']
            );
        return $canProcessTask;
    }

    /**
     * Return the appropriate processing helper
     *
     * This method adds a new helper type for image compression
     *
     * @param string $taskName
     * @return LocalCropScaleMaskCompressHelper|LocalCropScaleMaskHelper|LocalPreviewHelper
     * @throws \InvalidArgumentException
     */
    protected function getHelperByTaskName($taskName)
    {
        try {
            return parent::getHelperByTaskName($taskName);
        } catch (\InvalidArgumentException $e) {
            if ($taskName == 'CropScaleMaskCompress') {
                return GeneralUtility::makeInstance(LocalCropScaleMaskCompressHelper::class, $this);
            }
            throw $e;
        }
    }
}
