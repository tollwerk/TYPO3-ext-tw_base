<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Mozjpeg image compressor
 */
class MozjpegCompressorService extends AbstractFileCompressorService
{
    /**
     * Name of the TypoScript key to enable this service
     *
     * @var bool|string|null
     */
    protected $typoscriptEnableKey = 'compressors.mozjpeg';

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
        $filePath = $processingResult['filePath'];
        $mozjpegCommand = 'mozjpeg -copy none '.CommandUtility::escapeShellArgument($filePath);
        $mozjpegCommand .= ' > '.CommandUtility::escapeShellArgument($filePath.'.optimized');

        $output = $returnValue = null;
        CommandUtility::exec($mozjpegCommand, $output, $returnValue);

        // If the JPEG could be optimized: Cleanup
        if (!$returnValue) {
            unlink($filePath) && rename($filePath.'.optimized', $filePath) && chmod($filePath, 0664);
        }

        return $filePath;
    }
}
