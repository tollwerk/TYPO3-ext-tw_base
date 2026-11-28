<?php

/**
 * Writes the styles into a mail of tw_base just before it is sent
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Mail\EventListener;

use Tollwerk\TwBase\Mail\StyleInliner;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Mail\Event\BeforeMailerSentMessageEvent;

/**
 * Writes the styles into a mail of tw_base just before it is sent
 *
 * An extension may change a mail after Mailer::compose(), such as with assign(), and FluidEmail then renders it
 * anew, without the styles. Writing them again when the mail is sent keeps every change and the styles.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[AsEventListener('tw-base/inline-styles')]
final readonly class InlineStyles
{
    /**
     * Constructor
     *
     * @param StyleInliner $inliner Writes the styles of the components into the HTML
     */
    public function __construct(private StyleInliner $inliner) {}

    /**
     * Write the styles into the mail
     *
     * @param BeforeMailerSentMessageEvent $event Event before a message is sent
     *
     * @return void
     */
    public function __invoke(BeforeMailerSentMessageEvent $event): void
    {
        $this->inliner->refresh($event->getMessage());
    }
}
