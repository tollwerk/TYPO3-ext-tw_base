<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
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

namespace Tollwerk\TwBase\ViewHelpers;

use Tollwerk\TwBase\Service\ImageService;
use TYPO3\CMS\Extbase\Service\ImageService as CoreImageService;

/**
 * Extended image view helper
 *
 * Use this viewhelper as a replacement for the standard Fluid <f:image> viewhelper. It basically switches
 * to the custom extended image service providing additional functionality
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * Image service
     *
     * @var ImageService
     */
    protected $imageService;
    /**
     * Extended image service
     *
     * @var ImageService
     */
    protected $extendedImageService;

    /**
     * Inject the standard image service
     *
     * This method just overrides the parent class' method without doing anything. Although we want to use
     * a custom image service, the method signatures have to be identical for compatibility reasons. Therefore
     * we have to inject the extended image service using a different method. Ugly but works. ;)
     *
     * @param CoreImageService $imageService
     *
     * @see injectExtendedImageService()
     *
     */
    public function injectImageService(CoreImageService $imageService): void
    {
        // Do nothing
    }

    /**
     * Inject the extended image service
     *
     * @param ImageService $extendedImageService
     */
    public function injectExtendedImageService(ImageService $extendedImageService): void
    {
        $this->imageService = $extendedImageService;
    }
}
