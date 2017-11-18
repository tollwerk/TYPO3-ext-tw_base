<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Abstract image compressor service
 */
abstract class AbstractFileCompressorService extends AbstractService
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
     * @param array $processingResult Image processing result
     * @param array $configuration Service configuration
     * @return bool Success
     */
    public function processFile(TaskInterface $task, array $processingResult, array $configuration = [])
    {
        return false;
    }
}
