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

use DOMDocument;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Primitive LQIP creation service
 */
class PrimitiveLqipService extends AbstractLqipService
{
    /**
     * Create a low-quality image preview
     *
     * @param string $imageUri     Source image URL
     * @param array $configuration Service configuration
     *
     * @return string LQIP URI
     */
    public function getImageLqip(string $imageUri, array $configuration = []): string
    {
        // Calculate the LQIP URI
        ksort($configuration);
        $configurationChecksum = md5(serialize($configuration));
        $imageUriParts         = pathinfo($imageUri);
        $lqipUri               = $imageUriParts['dirname'].'/'.$imageUriParts['filename'].'_'.$configurationChecksum.'.svg';

        // If the LQIP file doesn't exist yet
        if (!file_exists(Environment::getPublicPath().'/'.$lqipUri)) {
            // Create abstract LQIP
            $primitiveCommand = 'primitive -i '.CommandUtility::escapeShellArgument(Environment::getPublicPath().'/'.$imageUri);
            $primitiveCommand .= ' -o '.CommandUtility::escapeShellArgument(Environment::getPublicPath().'/'.$lqipUri);
            $primitiveCommand .= ' -m '.intval(empty($configuration['mode']) ? 3 : $configuration['mode']);
            $primitiveCommand .= ' -n '.max(4, intval(empty($configuration['num']) ? 0 : $configuration['num']));

            $output = $returnValue = null;
            CommandUtility::exec($primitiveCommand, $output, $returnValue);
            if ($returnValue) {
                return false;
            }

            // Optimize SVG
            $svgoCommand = 'svgo --multipass -q -i '.CommandUtility::escapeShellArgument(Environment::getPublicPath().'/'.$lqipUri);

            $output = $returnValue = null;
            CommandUtility::exec($svgoCommand, $output, $returnValue);
            if ($returnValue) {
                return false;
            }

            // Optionally add a blur filter to the LQIP
            if (!empty($configuration['blur'])) {
                $svgDom = new DOMDocument();
                $svgDom->load(Environment::getPublicPath().'/'.$lqipUri);

                $blur = $svgDom->createElement('feGaussianBlur');
                $blur->setAttribute('stdDeviation', $configuration['blur']);
                $filter = $svgDom->createElement('filter');
                $filter->setAttribute('id', 'b');
                $filter->appendChild($blur);
                $svgDom->documentElement->insertBefore($filter, $svgDom->documentElement->firstChild);
                $svgDom->documentElement->lastChild->setAttribute('filter', 'url(#b)');

                $svgDom->save(Environment::getPublicPath().'/'.$lqipUri);
            }
        }

        return $lqipUri;
    }
}
