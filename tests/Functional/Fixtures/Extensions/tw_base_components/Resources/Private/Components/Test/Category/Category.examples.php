<?php

/**
 * Examples of a category from the database
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

return [
    'examples' => [
        'default' => [
            'label' => 'Default',
            'arguments' => [
                'category' => ['record' => 'sys_category', 'uid' => 1],
                'more' => [['record' => 'sys_category', 'uid' => 3]],
            ],
        ],
        'german' => [
            'label' => 'German',
            'page' => 2,
            'language' => 1,
            'arguments' => ['category' => ['record' => 'sys_category', 'uid' => 1]],
        ],
        'missing' => [
            'label' => 'Missing',
            'arguments' => ['category' => ['record' => 'sys_category', 'uid' => 99]],
        ],
    ],
];
