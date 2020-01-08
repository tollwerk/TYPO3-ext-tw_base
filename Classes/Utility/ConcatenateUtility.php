<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
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

namespace Tollwerk\TwBase\Utility;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\ResourceCompressor;

/**
 * Custom JavaScript concatenation utility
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class ConcatenateUtility extends ResourceCompressor
{
    /**
     * Concatenate JavaScript resources
     *
     * @param array $cssFiles
     */
    public function concatenateCssFiles(array $cssFiles): void
    {
        foreach (['cssFiles'] as $section) {
            $sectionBundles      = $this->splitBundles($cssFiles[$section]);

            $sectionConcatenates = [];
            foreach ($sectionBundles as $bundleResources) {
                $sectionConcatenates += parent::concatenateCssFiles($bundleResources);
            }

            $cssFiles[$section] = $this->replaceFonts($sectionConcatenates);
        }
    }
 
    /**
     * Concatenate JavaScript resources
     *
     * @param array $params              Parameters
     * @param PageRenderer $pageRenderer Page renderer
     */
    public function concatenateJs(array $params, PageRenderer $pageRenderer): void
    {
        foreach (['jsLibs', 'jsFiles', 'jsFooterFiles'] as $section) {
            $sectionBundles      = $this->splitBundles($params[$section]);
            $sectionConcatenates = [];
            foreach ($sectionBundles as $bundleResources) {
                $sectionConcatenates += parent::concatenateJsFiles($bundleResources);
            }
            $params[$section] = $sectionConcatenates;
        }
    }

    /**
     * Split a set of resources into bundles
     *
     * @param array $resources Resources
     *
     * @return array[] Bundled resources
     */
    protected function splitBundles(array $resources): array
    {
        // Split resources by bundle name
        $bundles = ['_' => []];
        foreach ($resources as $key => $resource) {
            $bundleName = '_';
            if (preg_match('/^(.*)#([^#]+)$/', $resource['file'], $bundleParams)) {
                $resource['file'] = $bundleParams[1];
                $bundleName       = trim($bundleParams[2]) ?: '_';
            }
            $bundles[$bundleName][$key] = $resource;
        }

        // Move the default bundle to the last position
        $defaultBundle = $bundles['_'];
        unset($bundles['_']);
        ksort($bundles);
        if (count($defaultBundle)) {
            $bundles[] = $defaultBundle;
        }

        return $bundles;
    }
}
