<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$fields = [
    'tx_twbase_heading_type' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type',
        'exclude' => 1,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.default', 0],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.main', 1],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.secondary', 2],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.medium', 3],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.small', 4],
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type.body', 5],
            ]
        ],
    ],
    'tx_twbase_inline' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_inline',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
            'default' => 0,
        ],
    ],
    'tx_twbase_lazyload' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_lazyload',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
            'default' => 1,
        ],
    ],
    'tx_twbase_responsive' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_responsive',
        'exclude' => 1,
        'config' => [
            'type' => 'check',
            'default' => 1,
        ],
    ],
    'tx_twbase_breakpoints' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_breakpoints',
        'exclude' => 1,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [['---', '']],
            'itemsProcFunc' => 'Tollwerk\\TwBase\\Utility\\TcaUtility->responsiveImagesBreakpointsSpecifications',
        ],
    ],
    'tx_twbase_skipconverter' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_skipconverter',
        'exclude' => 1,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingleBox',
            'size' => 2,
            'maxitems' => 99,
            'items' => [
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_skipconverter.I.webp', 'webp']
            ],
        ],
    ],
];

// Add new fields to pages:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $fields);

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
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'imagelinks',
    'tx_twbase_skipconverter,tx_twbase_lazyload,tx_twbase_responsive,tx_twbase_breakpoints,tx_twbase_inline',
    'before:image_zoom'
);
