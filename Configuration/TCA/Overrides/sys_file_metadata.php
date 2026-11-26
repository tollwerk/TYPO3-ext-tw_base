<?php

/**
 * Attribution and licence of files
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

use Tollwerk\TwBase\FileMetadata\License;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

// Creator and source are fields of the core extension filemetadata; these complete the attribution
ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', [
    'tx_twbase_creator_url' => [
        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_creator_url',
        'config' => [
            'type' => 'link',
            'allowedTypes' => ['url'],
        ],
    ],
    'tx_twbase_creation_year' => [
        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_creation_year',
        'config' => [
            'type' => 'number',
            'range' => ['lower' => 0, 'upper' => 9999],
            'default' => 0,
        ],
    ],
    'tx_twbase_license' => [
        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_license',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => '', 'value' => ''],
                ...array_map(
                    static fn(License $license): array => [
                        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_license.' . strtolower($license->value),
                        'value' => $license->value,
                    ],
                    License::cases(),
                ),
            ],
            'default' => '',
        ],
    ],
    'tx_twbase_license_name' => [
        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_license_name',
        'displayCond' => 'FIELD:tx_twbase_license:=:' . License::Custom->value,
        'config' => [
            'type' => 'input',
            'eval' => 'trim',
        ],
    ],
    'tx_twbase_license_url' => [
        'label' => 'tw_base.db:sys_file_metadata.tx_twbase_license_url',
        'displayCond' => 'FIELD:tx_twbase_license:=:' . License::Custom->value,
        'config' => [
            'type' => 'link',
            'allowedTypes' => ['url'],
        ],
    ],
]);
ExtensionManagementUtility::addFieldsToPalette(
    'sys_file_metadata',
    'twbaseAttribution',
    'tx_twbase_creator_url, tx_twbase_creation_year, --linebreak--, '
        . 'tx_twbase_license, tx_twbase_license_name, tx_twbase_license_url',
);
ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    '--palette--;tw_base.db:sys_file_metadata.palette.attribution;twbaseAttribution',
    '',
    'after:creator',
);
