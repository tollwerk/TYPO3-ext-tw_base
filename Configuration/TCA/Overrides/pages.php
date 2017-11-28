<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$fields = [
    'tx_twbase_title_language' => [
        'label' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_title_language',
        'exclude' => 1,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_title_language.auto', 0],
            ],
            'foreign_table' => 'static_languages',
            'allowNonIdValues' => true,
            'foreign_table_where' => 'AND static_languages.lg_sacred = 0 ORDER BY static_languages.lg_name_en',
            'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\FormDataProvider\\TcaSelectItemsProcessor->translateLanguagesSelector',
            'itemsProcFunc_config' => array(
                'indexField' => 'lg_iso_2,lg_country_iso_2',
            ),
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1
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
