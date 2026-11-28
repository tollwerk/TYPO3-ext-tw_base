<?php

/**
 * Examples of the title of the tests, within a section
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
    'requires' => ['twc:test.section'],
    'examples' => [
        'default' => [
            'label' => 'Default',
            'within' => ['twc:test.section', 'default'],
            'arguments' => ['title' => 'Inner'],
        ],
    ],
];
