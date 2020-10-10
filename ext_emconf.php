<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "tw_base"
 *
 * Auto generated by Extension Builder 2017-10-29
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['tw_base'] = [
    'title'            => 'tollwerk Base',
    'description'      => 'Collection of building blocks and view helpers for TYPO3 projects made by tollwerk',
    'category'         => 'misc',
    'author'           => 'Joschi Kuphal',
    'author_email'     => 'joschi@tollwerk.de',
    'state'            => 'stable',
    'internal'         => '',
    'uploadfolder'     => '0',
    'createDirs'       => '',
    'clearCacheOnLoad' => 0,
    'version'          => '4.3.1',
    'constraints'      => [
        'depends'   => [
            'typo3' => '10.4.9-',
            'form'  => '*'
        ],
        'conflicts' => [],
        'suggests'  => [
            'static_info_tables' => '*',
        ],
    ],
];
