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

use TYPO3\CMS\Core\Resource\Processing\AbstractGraphicalTask;

/**
 * Extended image processing task
 */
class ImageConvertTask extends AbstractGraphicalTask
{
    /**
     * Task type
     *
     * @var string
     */
    protected $type = 'Image';
    /**
     * Task name
     *
     * @var string
     */
    protected $name = 'Convert';

    /**
     * Returns the name the processed file should have in the filesystem.
     *
     * @return string Converted file name
     */
    public function getTargetFileName()
    {
        return 'conv_'.parent::getTargetFilename();
    }

    /**
     * Returns TRUE if the file has to be processed at all, such as e.g. the original file does.
     *
     * @return bool Whether the file needs processing
     * @todo Implement fileNeedsProcessing() method.
     */
    public function fileNeedsProcessing()
    {
        return false;
    }

    /**
     * Gets the file extension the processed file should
     * have in the filesystem by either using the configuration
     * setting, or the extension of the original file.
     *
     * @return string Target file extension
     */
    protected function determineTargetFileExtension()
    {
        if (empty($this->configuration['fileExtension'])) {
            switch ($this->configuration['converter']['type']) {
                case 'webp':
                    return 'webp';
            }
        }

        return parent::determineTargetFileExtension();
    }

    /**
     * Checks if the given configuration is sensible for this task, i.e. if all required parameters
     * are given, within the boundaries and don't conflict with each other.
     *
     * @param array $configuration
     *
     * @return bool Whether the configuration is valid
     * @todo Implement isValidConfiguration() method.
     */
    protected function isValidConfiguration(array $configuration)
    {
        return true;
    }
}
