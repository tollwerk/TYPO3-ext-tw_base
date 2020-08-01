<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Configuration
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

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$fields = [
    'tx_twbase_title_language' => [
        'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_title_language',
        'exclude' => 1,
        'config'  => [
            'type'       => 'select',
            'renderType' => 'selectSingle',
            'items'      => \Tollwerk\TwBase\Utility\TcaUtility::languages(),
            'size'       => 1,
            'minitems'   => 0,
            'maxitems'   => 1
        ],
    ],
    'tx_twbase_subnav_hide'    => [
        'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_subnav_hide',
        'exclude' => 1,
        'config'  => [
            'type'       => 'check',
            'renderType' => 'checkboxToggle',
            'default'    => 1,
            'items'      => [
                [
                    0                    => '',
                    1                    => '',
                    'invertStateDisplay' => true
                ]
            ],
        ]
    ],
    'tx_twbase_seo_title'      => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_seo_title',
        'config' => [
            'type' => 'user',
            'renderType' => 'seoTitleElement',
        ],
    ],
];

// Add new fields to pages:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $fields);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'title',
    'tx_twbase_title_language',
    'after:title'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'visibility',
    'tx_twbase_subnav_hide',
    'after:nav_hide'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    'tw_base',
    'Configuration/TypoScript/Main/TSconfig/page.tsconfig',
    'Page Settings'
);

// Add the 'tx_twbase_seo_title' field
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_twbase_seo_title',
    '',
    'after:subtitle'
);
