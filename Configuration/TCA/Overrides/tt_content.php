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

// Add new fields to content elements:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    [
        'tx_twbase_heading_language' => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_language',
            'exclude' => 1,
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => \Tollwerk\TwBase\Utility\TcaUtility::$languages,
                'size'       => 1,
                'minitems'   => 0,
                'maxitems'   => 1
            ],
        ],
        'tx_twbase_heading_type'  => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type',
            'exclude' => 1,
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.default',
                        0
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.xx-large',
                        1
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.x-large',
                        2
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.large',
                        3
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.medium',
                        4
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.small',
                        5
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.x-small',
                        6
                    ],
                ]
            ],
        ],
        'tx_twbase_inline'        => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_inline',
            'exclude' => 1,
            'config'  => [
                'type'    => 'check',
                'default' => 0,
            ],
        ],
        'tx_twbase_lazyload'      => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_lazyload',
            'exclude' => 1,
            'config'  => [
                'type'    => 'check',
                'default' => 1,
            ],
        ],
        'tx_twbase_responsive'    => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_responsive',
            'exclude' => 1,
            'config'  => [
                'type'    => 'check',
                'default' => 1,
            ],
        ],
        'tx_twbase_breakpoints'   => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_breakpoints',
            'exclude' => 1,
            'config'  => [
                'type'          => 'select',
                'renderType'    => 'selectSingle',
                'items'         => [['---', '']],
                'itemsProcFunc' => 'Tollwerk\\TwBase\\Utility\\TcaUtility->responsiveImagesBreakpointsSpecifications',
            ],
        ],
        'tx_twbase_skipconverter' => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_skipconverter',
            'exclude' => 1,
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingleBox',
                'size'       => 2,
                'maxitems'   => 99,
                'items'      => [
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_skipconverter.I.webp',
                        'webp'
                    ]
                ],
            ],
        ],
        'tx_twbase_video_tracks' => [
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_video_tracks',
            'exclude' => 1,
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_twbase_domain_model_video_track',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 9999,
            ]
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'header',
    'tx_twbase_heading_language',
    'after:header'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'headers',
    'tx_twbase_heading_language',
    'after:header'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'header',
    'tx_twbase_heading_type',
    'after:header_layout'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'headers',
    'tx_twbase_heading_type',
    'after:header_layout'
);

// Add the image inlining field to the imagelinks palette
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'imagelinks',
    'tx_twbase_inline',
    'after:image_zoom'
);

// Create a new 'imageadvanced' palette
$GLOBALS['TCA']['tt_content']['palettes']['imageadvanced'] = [
    'label'    => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.palette.imageadvanced',
    'showitem' => 'tx_twbase_responsive,tx_twbase_breakpoints,tx_twbase_lazyload,tx_twbase_skipconverter',
];

// Add the 'imageadvanced' palette to image content types
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--palette--;;imageadvanced',
    'textpic,image',
    'after:--palette--;;imagelinks'
);


/**
 * Video content element
 */

// Adds the image content element to the "Type" dropdown
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
    "tt_content",
    "CType",
    [
        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:twbase_video',
        'twbase_video',
        'content-video'
    ],
    "textmedia",
    "after"
);

// Configure the default backend fields for the video content element
$GLOBALS['TCA']['tt_content']['types']['twbase_video'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
            bodytext;LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:twbase_video.bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,
            image;LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:twbase_video.poster_image,
            tx_twbase_video_tracks,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ',
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];
