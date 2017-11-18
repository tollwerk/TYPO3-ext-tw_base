<?php

namespace Tollwerk\TwBase\Service\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\ImageCropScaleMaskTask;

/**
 * Extended image processing task
 */
class ImageCropScaleMaskCompressTask extends ImageCropScaleMaskTask
{
    /**
     * @var string
     */
    protected $name = 'CropScaleMaskCompress';

    /**
     * Gets the file extension the processed file should
     * have in the filesystem by either using the configuration
     * setting, or the extension of the original file.
     *
     * @return string
     */
    protected function determineTargetFileExtension()
    {
        if (empty($this->configuration['fileExtension']) && ($this->getSourceFile()->getExtension() == 'svg')) {
            return 'svg';
        }

        return parent::determineTargetFileExtension();
    }
}
