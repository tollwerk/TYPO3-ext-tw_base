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

$GLOBALS['TCA']['sys_file_metadata']['columns']['alternative']['config']['eval'] = 'required';

$newColumns = [
    'tx_twbase_author'        => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_author',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_twbase_author_url'    => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_author_url',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_twbase_creation_year' => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_creation_year',
        'config' => [
            'type' => 'input',
            'size' => 7,
            'eval' => 'trim,int'
        ],
    ],
    'tx_twbase_source_url'    => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_source_url',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_twbase_license' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'items' => [
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.none', ''],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.all', 'all'],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.cc0', 'CC0'],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by', 'BY'],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by-sa', 'BY-SA'],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by-nd', 'BY-ND'],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by-nc', 'BY-NC'],
                [
                    'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by-nc-sa',
                    'BY-NC-SA'
                ],
                [
                    'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.by-nc-nd',
                    'BY-NC-ND'
                ],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license.custom', 'custom'],
            ]
        ],
    ],
    'tx_twbase_license_name'    => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license_name',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_twbase_license_url'    => [
        'label'  => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tx_twbase_license_url',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
];

// Create a new 'imageadvanced' palette
$GLOBALS['TCA']['sys_file_metadata']['palettes']['author'] = [
    'showitem' => 'tx_twbase_author, tx_twbase_author_url',
];
$GLOBALS['TCA']['sys_file_metadata']['palettes']['creation'] = [
    'showitem' => 'tx_twbase_creation_year, tx_twbase_source_url',
];
$GLOBALS['TCA']['sys_file_metadata']['palettes']['license'] = [
    'showitem' => 'tx_twbase_license_name, tx_twbase_license_url',
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $newColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata',
    '--div--;LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.tabs.license,
    --palette--;;author,
    --palette--;;creation,
    tx_twbase_license,
    --palette--;;license',
    '',
    'after:title');
