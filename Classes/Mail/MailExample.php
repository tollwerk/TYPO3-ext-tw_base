<?php

/**
 * An example of a mail, with the data to compose it
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Mail;

/**
 * An example of a mail, with the data to compose it
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class MailExample
{
    /**
     * Constructor
     *
     * @param string               $name      Name, unique within the mail
     * @param string               $label     Label for people
     * @param string               $subject   Subject
     * @param array<string, mixed> $variables Variables of the template
     */
    public function __construct(
        public string $name,
        public string $label,
        public string $subject,
        public array $variables = [],
    ) {}
}
