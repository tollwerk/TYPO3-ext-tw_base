<?php

/**
 * tollwerk
 *
 * @category Jkphl
 * @package Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Service
 * @author Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Webp converter service
 */
class WebpConverterService extends AbstractFileConverterService
{
    /**
     * Name of the TypoScript key to enable this service
     *
     * @var bool|string|null
     */
    protected $typoscriptEnableKey = 'converters.webp';

    /**
     * Process a file
     *
     * @param string $filePath File Path
     * @param array $configuration Service configuration
     * @return bool Success
     */
    public function processFile($filePath, array $configuration = [])
    {
        $targetFilePath = dirname($filePath).DIRECTORY_SEPARATOR.pathinfo($filePath, PATHINFO_FILENAME).'.webp';

        $cwebpCommand = 'cwebp -q '.CommandUtility::escapeShellArgument($configuration['quality']);
        $cwebpCommand .= ' '.CommandUtility::escapeShellArgument($filePath);
        $cwebpCommand .= ' -o '.CommandUtility::escapeShellArgument($targetFilePath);

        $output = $returnValue = null;
        CommandUtility::exec($cwebpCommand, $output, $returnValue);

        return $returnValue ? '' : $targetFilePath;
    }
}