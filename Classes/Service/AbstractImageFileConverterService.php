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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Abstract file converter service
 */
abstract class AbstractImageFileConverterService extends AbstractService
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
     * @throws Exception
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

        return (boolean)$imageService->getImageSettings($this->typoscriptEnableKey.'._typoScriptNodeValue');
    }

    /**
     * Check whether this converer accepts a particular file for conversion
     *
     * @param FileInterface $image File
     *
     * @return bool File is accepted for conversion
     */
    public function acceptsFile(FileInterface $image): bool
    {
        return false;
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
        return null;
    }
}
