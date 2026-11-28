<?php

/**
 * A heading and the level it leaves behind
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Heading;

/**
 * A heading and the level it leaves behind
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class HeadingContext
{
    /**
     * Constructor
     *
     * @param string        $marker     Marker to restore the heading levels to the state before this heading
     * @param positive-int  $level      Level of the heading
     * @param string        $visualType Visual type of the heading, such as "large"
     * @param integer       $afterLevel Level after the heading and its section
     * @param boolean       $hidden     Whether the heading is only for assistive technology
     * @param list<integer> $skipped    Levels the heading skips, which is an error in the outline
     * @param integer       $type       Visual type of the heading as a level
     */
    public function __construct(
        public string $marker,
        public int $level,
        public string $visualType,
        public int $afterLevel,
        public bool $hidden = false,
        public array $skipped = [],
        public int $type = 1,
    ) {}
}
