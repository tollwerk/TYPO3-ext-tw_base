<?php

/**
 * Starts the heading levels over once a page is rendered
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Heading\EventListener;

use Tollwerk\TwBase\Heading\HeadingContextManager;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use TYPO3\CMS\Frontend\Event\BeforePageIsResolvedEvent;

/**
 * Starts the heading levels over before and after a page is rendered
 *
 * One process can render several pages, such as subrequests or tests. An error page can even be rendered while
 * another page is, such as for a record that is not found, and must not continue its levels.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[AsEventListener('tw-base/reset-heading-context-before', event: BeforePageIsResolvedEvent::class)]
#[AsEventListener('tw-base/reset-heading-context', event: AfterCacheableContentIsGeneratedEvent::class)]
final readonly class ResetHeadingContext
{
    /**
     * Constructor
     *
     * @param HeadingContextManager $headingContextManager Heading levels
     */
    public function __construct(private HeadingContextManager $headingContextManager) {}

    /**
     * Start the heading levels over
     *
     * @return void
     */
    public function __invoke(): void
    {
        $this->headingContextManager->reset();
    }
}
