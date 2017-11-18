<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use Tollwerk\TwBase\Service\AbstractFileConverterService;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Local convert helper
 */
class LocalConvertHelper
{
    /**
     * This method actually does the processing of files locally
     *
     * @param TaskInterface $task
     * @return array|NULL
     */
    public function process(TaskInterface $task)
    {
        $result = null;
        $config = $task->getConfiguration();
        $fileConverter = GeneralUtility::makeInstanceService('fileconvert', $config['converter']);
        if ($fileConverter instanceof AbstractFileConverterService) {
            $result = $fileConverter->processFile($task, $config);
        }
        return $result;
    }
}
