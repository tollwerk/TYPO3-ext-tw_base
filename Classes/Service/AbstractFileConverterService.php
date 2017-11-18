<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract file converter service
 */
abstract class AbstractFileConverterService extends AbstractService
{
    /**
     * Name of the TypoScript key to enable this service
     *
     * @var bool|string|null
     */
    protected $typoscriptEnableKey = false;

    /**
     * Initialization of the service
     *
     * Checks whether the service was enabled via its TypoScript constant
     */
    public function init()
    {
        if (!parent::init() || !$this->typoscriptEnableKey) {
            return false;
        }

        if ($this->typoscriptEnableKey === null) {
            return true;
        }

        /** @var ImageService $imageService */
        $imageService = GeneralUtility::makeInstance(ImageService::class);
        return (boolean)$imageService->getImageSettings($this->typoscriptEnableKey);
    }

    /**
     * Process a file
     *
     * @param TaskInterface $task Image processing task
     * @param array $configuration Service configuration
     * @return array Result
     */
    public function processFile(TaskInterface $task, array $configuration = [])
    {
        return false;
    }
}
