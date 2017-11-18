<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * SVGO image compressor
 */
class SvgoCompressorService extends AbstractFileCompressorService
{
    /**
     * Name of the TypoScript key to enable this service
     *
     * @var bool|string|null
     */
    protected $typoscriptEnableKey = 'compressors.svgo';

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
        $filePath = $task->getSourceFile()->getForLocalProcessing();
        $this->registerTempFile($filePath);

        $svgoConfig = json_encode($configuration, JSON_FORCE_OBJECT);
        $svgoCommand = 'svgo --quiet --multipass --input '.CommandUtility::escapeShellArgument($filePath);
        $svgoCommand .= ' --config '.CommandUtility::escapeShellArgument($svgoConfig);

        $output = $returnValue = null;
        CommandUtility::exec($svgoCommand, $output, $returnValue);

        return $returnValue ? '' : $filePath;
    }
}
