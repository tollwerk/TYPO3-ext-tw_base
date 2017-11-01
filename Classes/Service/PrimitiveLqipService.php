<?php

namespace Tollwerk\TwBase\Service;

use TYPO3\CMS\Core\Utility\CommandUtility;

/**
 * Primitive LQIP creation service
 */
class PrimitiveLqipService extends AbstractLqipService
{
    /**
     * Create a low-quality image preview
     *
     * @param string $imageUri Source image URL
     * @param array $configuration Service configuration
     * @return string LQIP URI
     */
    public function getImageLqip($imageUri, array $configuration = [])
    {
        // Calculate the LQIP URI
        ksort($configuration);
        $configurationChecksum = md5(serialize($configuration));
        $imageUriParts = pathinfo($imageUri);
        $lqipUri = $imageUriParts['dirname'].'/'.$imageUriParts['filename'].'_'.$configurationChecksum.'.svg';

        // If the LQIP file doesn't exist yet
        if (!file_exists(PATH_site.$lqipUri)) {
            // Create abstract LQIP
            $primitiveCommand = 'primitive -i '.CommandUtility::escapeShellArgument(PATH_site.$imageUri);
            $primitiveCommand .= ' -o '.CommandUtility::escapeShellArgument(PATH_site.$lqipUri);
            $primitiveCommand .= ' -m '.intval(empty($configuration['mode']) ? 3 : $configuration['mode']);
            $primitiveCommand .= ' -n '.max(4, intval(empty($configuration['num']) ? 0 : $configuration['num']));

            $output = $returnValue = null;
            CommandUtility::exec($primitiveCommand, $output, $returnValue);
            if ($returnValue) {
                return false;
            }

            // Optimize SVG
            $svgoCommand = 'svgo --multipass -q -i '.CommandUtility::escapeShellArgument(PATH_site.$lqipUri);

            $output = $returnValue = null;
            CommandUtility::exec($svgoCommand, $output, $returnValue);
            if ($returnValue) {
                return false;
            }

            // Optionally add a blur filter to the LQIP
            if (!empty($configuration['blur'])) {
                $svgDom = new \DOMDocument();
                $svgDom->load(PATH_site.$lqipUri);

                $blur = $svgDom->createElement('feGaussianBlur');
                $blur->setAttribute('stdDeviation', $configuration['blur']);
                $filter = $svgDom->createElement('filter');
                $filter->setAttribute('id', 'b');
                $filter->appendChild($blur);
                $svgDom->documentElement->insertBefore($filter, $svgDom->documentElement->firstChild);
                $svgDom->documentElement->lastChild->setAttribute('filter', 'url(#b)');

                $svgDom->save(PATH_site.$lqipUri);
            }
        }

        return $lqipUri;
    }
}
