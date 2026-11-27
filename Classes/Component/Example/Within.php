<?php

/**
 * The example of another component that an example appears in
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\Example;

/**
 * The example of another component that an example appears in, and the slot
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Within
{
    /**
     * Constructor
     *
     * @param string $component Component with its prefix, such as "twc:event.list"
     * @param string $example   Name of its example
     * @param string $slot      Slot of that example
     */
    public function __construct(public string $component, public string $example, public string $slot = 'default') {}
}
