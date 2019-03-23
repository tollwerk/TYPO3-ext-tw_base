<?php

/**
 * NDF
 *
 * @category   Artefakt
 * @package    Artefakt\Core
 * @subpackage Tollwerk\TwBase\ViewHelpers
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2018 tollwerk GmbH <info@tollwerk.de>
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

/**
 * Extended image view helper
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * Image service
     *
     * @var \Tollwerk\TwBase\Service\ImageService
     */
    protected $imageService;
    /**
     * Extended image service
     *
     * @var \Tollwerk\TwBase\Service\ImageService
     */
    protected $extendedImageService;

    /**
     * Inject the standard image service
     *
     * This method just overrides the parent class' method without doing anything. Although we want to use
     * a custom image service, the method signatures have to be identical for compatibility reasons. Therefore
     * we have to inject the extended image service using a different method. Ugly but works. ;)
     *
     * @see injectExtendedImageService()
     *
     * @param \TYPO3\CMS\Extbase\Service\ImageService $imageService
     */
    public function injectImageService(\TYPO3\CMS\Extbase\Service\ImageService $imageService)
    {
        // Do nothing
    }

    /**
     * Inject the extended image service
     *
     * @param \Tollwerk\TwBase\Service\ImageService $extendedImageService
     */
    public function injectExtendedImageService(\Tollwerk\TwBase\Service\ImageService $extendedImageService)
    {
        $this->imageService = $extendedImageService;
    }
}
