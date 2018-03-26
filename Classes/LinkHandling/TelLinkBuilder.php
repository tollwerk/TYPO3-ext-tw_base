<?php

namespace Tollwerk\TwBase\LinkHandling;

use TYPO3\CMS\Backend\Form\Element\InputLinkElement;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Link builder for tel links
 */
class TelLinkBuilder
{
    /**
     * Render a tel link preview
     *
     * @param array $linkData
     * @param $linkParts
     * @param array $data
     * @param InputLinkElement $inputLinkElement
     * @return array
     */
    public function getFormData(array $linkData, $linkParts, array $data, InputLinkElement $inputLinkElement)
    {
        return [
            'text' => $linkData['value'],
            'icon' => GeneralUtility::makeInstance(IconFactory::class)->getIcon(
                'tx-base-tel', Icon::SIZE_SMALL
            )->render()
        ];
    }
}