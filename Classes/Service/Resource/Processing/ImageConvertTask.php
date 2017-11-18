<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\AbstractGraphicalTask;

/**
 * Extended image processing task
 */
class ImageConvertTask extends AbstractGraphicalTask
{
    /**
     * @var string
     */
    protected $type = 'Image';
    /**
     * @var string
     */
    protected $name = 'Convert';

    public function fileNeedsProcessing()
    {
        // @todo Implement fileNeedsProcessing() method.
    }

    /**
     * Gets the file extension the processed file should
     * have in the filesystem by either using the configuration
     * setting, or the extension of the original file.
     *
     * @return string
     */
    protected function determineTargetFileExtension()
    {
        if (empty($this->configuration['fileExtension'])) {
            switch ($this->configuration['converter']) {
                case 'webp':
                    return 'webp';
            }
        }

        return parent::determineTargetFileExtension();
    }

    /**
     * Checks if the given configuration is sensible for this task, i.e. if all required parameters
     * are given, within the boundaries and don't conflict with each other.
     *
     * @param array $configuration
     * @return bool
     */
    protected function isValidConfiguration(array $configuration)
    {
        // @todo Implement isValidConfiguration() method.
    }
}
