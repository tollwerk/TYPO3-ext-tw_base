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
                'default' => '<p class="MailParagraph">Hello,</p>',
                'footer' => '<div class="MailFooter"><p class="MailFooter__line">Example Organisation</p></div>',
            ],
        ],
    ],
];
