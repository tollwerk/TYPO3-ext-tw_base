<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Mozjpeg image compressor
 */
class MozjpegCompressorService extends AbstractFileCompressorService
{
    /**
     * Compress a file
     *
     * @param string $fileUri Source file URL
     * @param array $configuration Service configuration
     * @return boolean Success
     */
    public function compressFile($fileUri, array $configuration = [])
    {
        $mozjpegCommand = 'mozjpeg -copy none '.CommandUtility::escapeShellArgument(PATH_site.$fileUri);
        $mozjpegCommand .= ' > '.CommandUtility::escapeShellArgument(PATH_site.$fileUri.'.optimized');

        $output = $returnValue = null;
        CommandUtility::exec($mozjpegCommand, $output, $returnValue);

        // If the JPEG could not be optimized: Error
        if ($returnValue) {
            return false;
        }

        // Cleanup
        return unlink(PATH_site.$fileUri)
            && rename(PATH_site.$fileUri.'.optimized', PATH_site.$fileUri)
            && chmod(PATH_site.$fileUri, 0664);
    }
}
