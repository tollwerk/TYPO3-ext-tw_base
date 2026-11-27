<?php

/**
 * Examples of the frame of a mail
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
            'arguments' => ['languageCode' => 'en-US', 'subject' => 'Your code', 'website' => 'Example Website'],
            'slots' => [
                'default' => '<p class="Mail-paragraph">Hello,</p>',
                'footer' => '<div class="Mail-footer"><p class="Mail-footer-line">Example Organisation</p></div>',
            ],
        ],
    ],
];
