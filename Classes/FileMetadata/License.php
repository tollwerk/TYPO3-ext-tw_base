<?php

/**
 * Licence of a file
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\FileMetadata;

/**
 * Licence of a file
 *
 * The values are those of earlier major versions. Creative Commons licences are taken as version 4.0, the current one,
 * since the files never stated a version.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
enum License: string
{
    case AllRightsReserved = 'all';
    case Cc0 = 'CC0';
    case By = 'BY';
    case BySa = 'BY-SA';
    case ByNd = 'BY-ND';
    case ByNc = 'BY-NC';
    case ByNcSa = 'BY-NC-SA';
    case ByNcNd = 'BY-NC-ND';
    case Custom = 'custom';

    /**
     * Version of the Creative Commons licences
     *
     * @var string
     */
    private const string CC_VERSION = '4.0';

    /**
     * Name of the licence
     *
     * @return string|null Name, or NULL for a custom licence, which names itself
     */
    public function title(): ?string
    {
        return match ($this) {
            self::AllRightsReserved => 'All rights reserved',
            self::Cc0 => 'CC0 1.0',
            self::Custom => null,
            default => 'CC ' . $this->value . ' ' . self::CC_VERSION,
        };
    }

    /**
     * Address of the licence text
     *
     * @return string|null Address, or NULL without a public licence text
     */
    public function url(): ?string
    {
        return match ($this) {
            self::AllRightsReserved, self::Custom => null,
            self::Cc0 => 'https://creativecommons.org/publicdomain/zero/1.0/',
            default => \sprintf(
                'https://creativecommons.org/licenses/%s/%s/',
                strtolower($this->value),
                self::CC_VERSION,
            ),
        };
    }
}
