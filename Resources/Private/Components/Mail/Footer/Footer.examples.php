<?php

/**
 * Examples of the footer of a mail
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
        'complete' => [
            'label' => 'With reason and links',
            'within' => ['twc:mail.frame', 'default'],
            'arguments' => [
                'sender' => 'Example Organisation, Example Street 1, 12345 Example City',
                'reason' => 'You get this email because someone entered your address to register.',
                'links' => [
                    ['label' => 'Write to us: hello@example.org', 'href' => 'mailto:hello@example.org'],
                    ['label' => 'Legal notice', 'href' => 'https://example.org/legal-notice'],
                ],
            ],
        ],
        'minimal' => [
            'label' => 'Sender only',
            'within' => ['twc:mail.frame', 'default'],
            'arguments' => ['sender' => 'Example Organisation'],
        ],
    ],
];
