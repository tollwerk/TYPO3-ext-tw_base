<?php

/**
 * Examples of a notice in a mail
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
    'requires' => ['twc:mail.frame'],
    'examples' => [
        'default' => [
            'label' => 'Default',
            'within' => ['twc:mail.frame', 'default'],
            'slots' => ['default' => '<p>If you did not ask for this code, ignore this email.</p>'],
        ],
    ],
];
