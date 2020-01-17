<?php

return [
    'ctrl'      => [
        'title'           => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track',
        'label'           => 'kind',
        'label_alt'       => 'language',
        'label_alt_force' => true,
        'tstamp'          => 'tstamp',
        'crdate'          => 'crdate',
        'cruser_id'       => 'cruser_id',
        'delete'          => 'deleted',
        'enablecolumns'   => [
            'disabled' => 'hidden',
        ],
        'searchFields'    => 'title,kind',
        'iconfile'        => 'EXT:tw_base/Resources/Public/Icons/Track.svg'
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden, title, kind, file, language',
    ],
    'types'     => [
        '1' => ['showitem' => '--palette--;;video, file, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime'],
    ],
    'palettes'  => [
        'video' => [
            'showitem'       => 'kind, language',
            'canNotCollapse' => true
        ],
    ],
    'columns'   => [
        'hidden' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.hidden_toggle',
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
        'kind'   => [
            'exclude' => false,
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.kind',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.kind.subtitles',
                        'subtitles',
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.kind.captions',
                        'captions',
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.kind.descriptions',
                        'descriptions',
                    ],
                    [
                        'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.kind.chapters',
                        'chapters',
                    ],
                ],
            ],
        ],

        'file' => [
            'exclude' => false,
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.file',
            'config'  => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'file',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.file.addFileReference',
                        'fileUploadAllowed'          => false,
                    ],
                    'maxitems'   => 1,
                ],
                'vtt'
            ),
        ],

        'language' => [
            'exclude' => false,
            'label'   => 'LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:tx_twbase_domain_model_video_track.language',
            'config'  => [
                'type'          => 'select',
                'renderType'    => 'selectSingle',
                'items'         => [],
                'itemsProcFunc' => \Tollwerk\TwBase\Utility\TcaUtility::class.'->trackLanguages',
            ],
        ],
    ],
];
