<?php

/**
 * Information about a link target
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

use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Information about a link target
 *
 * Templates use it to describe a link before it is followed, such as the host it leads to or the type and size of a
 * file.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class LinkInfo
{
    /**
     * Constructor
     *
     * @param string             $type     Type of target as TYPO3 names it, such as "page", "url", "email" or "file"
     * @param string             $target   Target as given, such as "t3://page?uid=1" or "https://example.org"
     * @param string|null        $host     Host of an external address or of an email address
     * @param string|null        $email    Email address
     * @param string|null        $phone    Phone number
     * @param integer|null       $page     Uid of a page
     * @param FileInterface|null $file     File
     * @param boolean            $external Whether the target is outside of the website
     */
    public function __construct(
        public string $type,
        public string $target,
        public ?string $host = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?int $page = null,
        public ?FileInterface $file = null,
        private bool $external = false,
    ) {}

    /**
     * Whether the link leaves the website
     *
     * @return boolean
     */
    public function isExternal(): bool
    {
        return $this->external;
    }
}
