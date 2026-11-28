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

use Closure;

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
     * Label of the name of the licence that is no proper name
     *
     * @var string
     */
    public const string ALL_RIGHTS_RESERVED = 'tw_base.messages:license.all';

    /**
     * Version of the Creative Commons licences
     *
     * @var string
     */
    private const string CC_VERSION = '4.0';

    /**
     * Name of the licence
     *
     * @param Closure(string): string $translate Translation of a label, such as LanguageService::sL()
     *
     * @return string|null Name, or NULL for a custom licence, which names itself
     */
    public function title(Closure $translate): ?string
    {
        // The names of the Creative Commons licences are proper names in every language
        return match ($this) {
            self::AllRightsReserved => $translate(self::ALL_RIGHTS_RESERVED),
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
