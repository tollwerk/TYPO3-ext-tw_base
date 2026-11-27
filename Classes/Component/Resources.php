<?php

/**
 * Resources of a component
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component;

/**
 * Resources of a component: a stylesheet, CSS for the head and an ES module, each optional
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Resources
{
    /**
     * Constructor
     *
     * @param string      $identifier Identifier for the asset collector, the same for every rendering of the component
     * @param string|null $styleSheet Stylesheet as a path with "EXT:"
     * @param string|null $critical   CSS that goes inline into the head
     * @param string|null $module     Specifier of the ES module in the import map
     */
    public function __construct(
        public string $identifier,
        public ?string $styleSheet = null,
        public ?string $critical = null,
        public ?string $module = null,
    ) {}
}
