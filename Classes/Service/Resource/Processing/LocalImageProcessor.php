<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
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
     *
     * @return bool
     */
    public function canProcessTask(TaskInterface $task)
    {
        $canProcessTask = $task->getType() === 'Image';
        $canProcessTask = $canProcessTask & in_array(
                $task->getName(),
                ['Preview', 'CropScaleMask', 'CropScaleMaskCompress', 'Convert']
            );

        return $canProcessTask;
    }

    /**
     * Return the appropriate processing helper
     *
     * This method adds new helper type for image compression & conversion
     *
     * @param string $taskName
     *
     * @return LocalCropScaleMaskCompressHelper|LocalConvertHelper|LocalCropScaleMaskHelper|LocalPreviewHelper
     * @throws \InvalidArgumentException
     */
    protected function getHelperByTaskName($taskName)
    {
        try {
            return parent::getHelperByTaskName($taskName);
        } catch (\InvalidArgumentException $e) {
            switch ($taskName) {
                case 'CropScaleMaskCompress':
                    return GeneralUtility::makeInstance(LocalCropScaleMaskCompressHelper::class, $this);
                case 'Convert':
                    return GeneralUtility::makeInstance(LocalConvertHelper::class, $this);
                default:
                    throw $e;
            }
        }
    }

    /**
     * @return GraphicalFunctions
     */
    protected function getGraphicalFunctionsObject()
    {
        static $graphicalFunctionsObject = null;

        if ($graphicalFunctionsObject === null) {
            $graphicalFunctionsObject = GeneralUtility::makeInstance(GraphicalFunctions::class);
            $graphicalFunctionsObject->init();
        }

        return $graphicalFunctionsObject;
    }
}
