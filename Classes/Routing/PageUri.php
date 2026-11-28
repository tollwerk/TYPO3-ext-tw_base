<?php

/**
 * The one address of the page a request renders
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Routing;

use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\Page\RootLineException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use UnexpectedValueException;

/**
 * The one address of the page a request renders
 *
 * Many addresses lead to the same page: with parameters that do not change it, such as utm_source or gclid, with a
 * trailing slash, or with ?id=. The page cache keeps what the first of them rendered for all later requests, so
 * whatever the page derives from its address, such as the ids of its structured data or og:url, comes from the
 * router: the page in its language with the arguments of its route, such as those of a record it shows, as the
 * canonical link of EXT:seo does. A request without a site or routing, such as in a test, has the address of the
 * request without query and fragment.
 *
 * The address of another page, such as the legal notice in the footer of a mail, comes only for a page that its site
 * shows: for a page it cannot load, because it is missing, deleted, hidden or expired, the router builds the address
 * of the start page instead of throwing.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class PageUri
{
    /**
     * Address of a page that its site shows
     *
     * @param Site                 $site       Site the page must belong to
     * @param integer              $page       Uid of the page
     * @param SiteLanguage|null    $language   Language, or NULL for the default language
     * @param array<string, mixed> $parameters Further parameters of the address
     *
     * @return UriInterface|null Address, or NULL if the page is not one the site shows
     *
     * @throws DBALException If the pages cannot be read
     * @throws InvalidArgumentException If the address cannot be built
     * @throws UnexpectedValueException If the page record cannot be read
     */
    public function ofPage(Site $site, int $page, ?SiteLanguage $language = null, array $parameters = []): ?UriInterface
    {
        // Created for each call, as it takes the visibility from the context when it is created
        $pages = GeneralUtility::makeInstance(PageRepository::class);
        if (($page <= 0) || ($pages->getPage($page) === [])) {
            return null;
        }

        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $page)->get();
            $uids = array_map(
                static fn(mixed $uid): int => \is_numeric($uid) ? (int) $uid : 0,
                array_column($rootLine, 'uid'),
            );
            if (!\in_array($site->getRootPageId(), $uids, true)) {
                return null;
            }

            if ($language instanceof SiteLanguage) {
                $parameters['_language'] = $language;
            }

            return $site->getRouter()->generateUri($page, $parameters);
        } catch (RootLineException|InvalidRouteArgumentsException) {
            return null;
        }
    }

    /**
     * Address of the page a request renders
     *
     * @param ServerRequestInterface $request Request
     *
     * @return UriInterface Absolute address without fragment
     */
    public function of(ServerRequestInterface $request): UriInterface
    {
        $site = $request->getAttribute('site');
        $routing = $request->getAttribute('routing');
        $fallback = $this->withoutQuery($request->getUri());
        if (!$site instanceof Site || !$routing instanceof PageArguments) {
            return $fallback;
        }

        $parameters = $routing->getRouteArguments();
        $language = $request->getAttribute('language');
        if ($language instanceof SiteLanguage) {
            $parameters['_language'] = $language;
        }

        if (!\in_array($routing->getPageType(), ['', '0'], true)) {
            $parameters['type'] = $routing->getPageType();
        }

        try {
            $router = $site->getRouter();

            return $router->generateUri($routing->getPageId(), $parameters, '', RouterInterface::ABSOLUTE_URL);
        } catch (InvalidRouteArgumentsException) {
            return $fallback;
        }
    }

    /**
     * Address without query and fragment
     *
     * @param UriInterface $uri Address
     *
     * @return UriInterface
     */
    private function withoutQuery(UriInterface $uri): UriInterface
    {
        try {
            return $uri->withQuery('')->withFragment('');
        } catch (InvalidArgumentException) {
            // An empty query and an empty fragment are valid in every address, so this never happens
            return $uri;
        }
    }
}
