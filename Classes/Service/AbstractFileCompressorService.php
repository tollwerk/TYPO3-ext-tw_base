<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Service\AbstractService;

/**
 * Abstract image compressor service
 */
abstract class AbstractFileCompressorService extends AbstractService
{
    /**
     * Compress a file
     *
     * @param string $fileUri Source file URL
     * @param array $configuration Service configuration
     * @return boolean Success
     */
    abstract public function compressFile($fileUri, array $configuration = []);
}
