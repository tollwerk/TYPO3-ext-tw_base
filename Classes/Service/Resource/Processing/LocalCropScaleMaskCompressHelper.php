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

namespace Tollwerk\TwBase\Service\Resource\Processing;

use Tollwerk\TwBase\Service\AbstractFileCompressorService;
use Tollwerk\TwBase\Service\ImageService;
use Tollwerk\TwBase\Utility\ArrayUtility;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Extended local CropScaleMask helper
 */
class LocalCropScaleMaskCompressHelper extends LocalCropScaleMaskHelper
{
    /**
     * This method actually does the processing of files locally
     *
     * @param TaskInterface $task
     *
     * @return array|NULL Processing result
     * @throws Exception
     */
    public function process(TaskInterface $task)
    {
        $result = parent::process($task);
        if ($result !== null) {
            $fileExtension  = strtolower($task->getSourceFile()->getExtension());
            $fileCompressor = GeneralUtility::makeInstanceService('filecompress', $fileExtension);
            if ($fileCompressor instanceof AbstractFileCompressorService) {
                /** @var ImageService $imageService */
                $imageService       = GeneralUtility::makeInstance(ImageService::class);
                $fileFormatSettings = ArrayUtility::recursivelyFalsify($imageService->getImageSettings('images.compress.'.$fileExtension));
                $result['filePath'] = $fileCompressor->processImageFile($task, $result, $fileFormatSettings);
            }
        }

        return $result;
    }
}
