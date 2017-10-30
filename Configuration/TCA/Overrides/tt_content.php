<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$fields = array(
    'tx_twbase_heading_type' => array(
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tt_content.tx_twbase_heading_type',
        'exclude' => 1,
        'config' => array(
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
        ),
    ),
);

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
