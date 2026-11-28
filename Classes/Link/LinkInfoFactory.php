<?php

/**
 * Resolves typolinks into information about their target
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Link;

use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Resolves typolinks into information about their target
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class LinkInfoFactory
{
    /**
     * Constructor
     *
     * @param TypoLinkCodecService $typoLinkCodecService Splits typolinks into their parts
     * @param LinkService          $linkService          Resolves link targets
     */
    public function __construct(
        private TypoLinkCodecService $typoLinkCodecService,
        private LinkService $linkService,
    ) {}

    /**
     * Information about the target of a typolink
     *
     * @param string    $typolink Typolink, such as "t3://page?uid=1 _blank" or "https://example.org"
     * @param Site|null $site     Site of the page, whose own addresses are no external links
     *
     * @return LinkInfo
     */
    public function create(string $typolink, ?Site $site = null): LinkInfo
    {
        $target = $this->typoLinkCodecService->decode($typolink)['url'];
        if ($target === '') {
            return new LinkInfo('none', '');
        }

        try {
            $data = $this->linkService->resolve($target);
        } catch (UnknownLinkHandlerException) {
            return new LinkInfo('unknown', $target);
        }

        $type = \is_string($data['type'] ?? null) ? $data['type'] : 'unknown';

        return match ($type) {
            'url' => $this->url($target, $data, $site),
            'email' => $this->email($target, $data),
            'telephone' => new LinkInfo($type, $target, phone: $this->string($data, 'telephone')),
            'page' => new LinkInfo($type, $target, page: (int) $this->string($data, 'pageuid')),
            'file' => new LinkInfo(
                $type,
                $target,
                file: ($data['file'] ?? null) instanceof FileInterface ? $data['file'] : null,
            ),
            default => new LinkInfo($type, $target),
        };
    }

    /**
     * Information about an address, which is external unless the site has its host
     *
     * @param string       $target Target
     * @param array<mixed> $data   Resolved target
     * @param Site|null    $site   Site of the page
     *
     * @return LinkInfo
     */
    private function url(string $target, array $data, ?Site $site): LinkInfo
    {
        $host = parse_url($this->string($data, 'url'), PHP_URL_HOST);
        $host = \is_string($host) ? strtolower($host) : null;

        return new LinkInfo(
            'url',
            $target,
            host: $host,
            external: (null !== $host) && !\in_array($host, $this->hosts($site), true),
        );
    }

    /**
     * Hosts of a site, of its base and of the bases of its languages
     *
     * @param Site|null $site Site
     *
     * @return list<string>
     */
    private function hosts(?Site $site): array
    {
        if (!$site instanceof Site) {
            return [];
        }

        $hosts = [$site->getBase()->getHost()];
        foreach ($site->getAllLanguages() as $language) {
            $hosts[] = $language->getBase()->getHost();
        }

        return array_values(array_unique(array_filter(
            array_map(strtolower(...), $hosts),
            static fn(string $host): bool => $host !== '',
        )));
    }

    /**
     * Information about an email address
     *
     * @param string       $target Target
     * @param array<mixed> $data   Resolved target
     *
     * @return LinkInfo
     */
    private function email(string $target, array $data): LinkInfo
    {
        $email = $this->string($data, 'email');
        $at = strrpos($email, '@');

        return new LinkInfo('email', $target, host: ($at === false) ? null : substr($email, $at + 1), email: $email);
    }

    /**
     * String value of the resolved target
     *
     * @param array<mixed> $data Resolved target
     * @param string       $key  Key
     *
     * @return string
     */
    private function string(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return \is_scalar($value) ? (string) $value : '';
    }
}
