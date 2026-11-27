<?php

/**
 * A variant or a state of a component
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
 * A variant or a state of a component, with the arguments and slots to render it
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Example
{
    /**
     * Constructor
     *
     * @param string                $name      Name, unique within the component
     * @param string                $label     Label for people
     * @param string|null           $group     Group, such as "Variants" or "States"
     * @param array<string, mixed>  $arguments Arguments
     * @param array<string, string> $slots     HTML of each slot, by the name of the slot
     * @param Within|null           $within    Example of another component in whose slot this example appears
     */
    public function __construct(
        public string $name,
        public string $label,
        public ?string $group = null,
        public array $arguments = [],
        public array $slots = [],
        public ?Within $within = null,
    ) {}
}
