<?php

/**
 * tollwerk
 *
 * @category   Jkphl
 * @package    Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Service
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Tollwerk\TwBase\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Webp converter service
 */
class WebpConverterService extends AbstractImageFileConverterService
{
    /**
     * Name of the TypoScript key to enable this service
     *
     * @var bool|string|null
     */
    protected $typoscriptEnableKey = 'converters.webp';

    /**
     * Check whether this converer accepts a particular file for conversion
     *
     * @param FileInterface $image File
     *
     * @return bool File is accepted for conversion
     */
    public function acceptsFile(FileInterface $image): bool
    {
        return in_array(strtolower($image->getExtension()), ResponsiveImagesUtility::SRCSET_FILE_EXTENSIONS);
    }

    /**
     * Process a file
     *
     * @param TaskInterface $task  Image processing task
     * @param array $configuration Service configuration
     *
     * @return array|null Result
     */
    public function processImageFile(TaskInterface $task, array $configuration = []): ?array
    {
        /** @var File $sourceFile */
        $sourceFile     = $task->getSourceFile();
        $sourceFilePath = $sourceFile->getForLocalProcessing();
        $this->registerTempFile($sourceFilePath);

        $targetFilePath = dirname($sourceFilePath).DIRECTORY_SEPARATOR.pathinfo(
                $sourceFilePath, PATHINFO_FILENAME
            ).'.webp';
        $this->registerTempFile($targetFilePath);

        $cwebpCommand = 'cwebp -q '.CommandUtility::escapeShellArgument($configuration['quality']);
        $cwebpCommand .= ' '.CommandUtility::escapeShellArgument($sourceFilePath);
        $cwebpCommand .= ' -o '.CommandUtility::escapeShellArgument($targetFilePath);

        $output = $returnValue = null;
        CommandUtility::exec($cwebpCommand, $output, $returnValue);

        return $returnValue ? null : ['filePath' => $targetFilePath];
    }
}
