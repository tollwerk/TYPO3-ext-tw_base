<?php

/**
 * Keeps the route of the current page in links to a fragment of it
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Link\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Frontend\Event\ModifyPageLinkConfigurationEvent;

/**
 * Keeps the route of the current page in links to a fragment of it
 *
 * TYPO3 resolves a link like "#tickets" in a text to the current page, but on a page whose address carries the
 * arguments of a plugin, such as the page of an event, it builds the address of the page without them: the link
 * leads to the page of the plugin, not to the section of the record shown. With the arguments of the current route,
 * the link leads to the same address and its fragment.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[AsEventListener('tw-base/keep-route-of-fragment-links')]
final readonly class KeepRouteOfFragmentLinks
{
    /**
     * Add the arguments of the current route to a link to a fragment of the current page
     *
     * @param ModifyPageLinkConfigurationEvent $event Event
     *
     * @return void
     */
    public function __invoke(ModifyPageLinkConfigurationEvent $event): void
    {
        $details = $event->getLinkDetails();
        $parameter = $details['typoLinkParameter'] ?? null;
        $page = $event->getPage()['uid'] ?? null;
        $routing = $event->getRequest()->getAttribute('routing');
        if (
            // Only a link given as a fragment of the current page alone, such as "#tickets"
            !is_string($parameter)
            || (preg_match('%^(t3://page\?uid=current)?#%', $parameter) !== 1)
            || ($event->getFragment() === '')
            || (($details['parameters'] ?? '') !== '')
            || !$routing instanceof PageArguments
            || !is_numeric($page)
            || ($routing->getPageId() !== (int) $page)
            || ($routing->getRouteArguments() === [])
        ) {
            return;
        }

        $arguments = array_replace_recursive($routing->getRouteArguments(), $event->getQueryParameters());
        $event->setQueryParameters($arguments);
    }
}
