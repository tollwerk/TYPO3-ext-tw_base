<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Service
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * SVGO image compressor
 */
class SvgoCompressorService extends AbstractImageFileCompressorService
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
     * @param TaskInterface $task     Image processing task
     * @param array $processingResult Image processing result
     * @param array $configuration    Service configuration
     *
     * @return string File path
     */
    public function processImageFile(TaskInterface $task, array $processingResult, array $configuration = []): string
    {
        $filePath = $task->getSourceFile()->getForLocalProcessing();
        $this->registerTempFile($filePath);

        $svgoConfig  = json_encode($configuration, JSON_NUMERIC_CHECK);
        $svgoCommand = 'svgo --quiet --multipass --input '.CommandUtility::escapeShellArgument($filePath);
        $svgoCommand .= ' --config '.CommandUtility::escapeShellArgument($svgoConfig);

        $output = $returnValue = null;
        CommandUtility::exec($svgoCommand, $output, $returnValue);

        return $returnValue ? '' : $filePath;
    }
}
