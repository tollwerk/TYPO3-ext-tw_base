<?php

/**
 * Mails of the tests
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
    'tw_base_components.code' => [
        'template' => 'TwBase/Test',
        'purpose' => 'Sends a code',
        'recipient' => 'The person who asked for it',
        'examples' => [
            'default' => ['label' => 'Default', 'subject' => 'Your code', 'variables' => ['code' => '482916']],
        ],
    ],
];
