<?php

/**
 * Who made a file and under which licence it may be used
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\FileMetadata;

use Tollwerk\TwBase\FileMetadata\Attribution;
use Tollwerk\TwBase\FileMetadata\License;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Who made a file and under which licence it may be used
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class AttributionTest extends UnitTestCase
{
    /**
     * A Creative Commons licence names itself and its text
     *
     * @return void
     */
    public function testCreativeCommonsLicence(): void
    {
        $attribution = Attribution::fromMetadata([
            'creator' => ' Ada Lovelace ',
            'tx_twbase_creator_url' => 'https://example.org/ada',
            'source' => 'https://example.org/photo',
            'tx_twbase_creation_year' => 1843,
            'tx_twbase_license' => 'BY-NC-SA',
            'tx_twbase_license_name' => 'Ignored',
        ]);

        self::assertNotNull($attribution);
        self::assertSame('Ada Lovelace', $attribution->creator);
        self::assertSame('https://example.org/ada', $attribution->creatorUrl);
        self::assertSame('https://example.org/photo', $attribution->source);
        self::assertSame(1843, $attribution->year);
        self::assertSame(License::ByNcSa, $attribution->license);
        self::assertSame('CC BY-NC-SA 4.0', $attribution->licenseName);
        self::assertSame('https://creativecommons.org/licenses/by-nc-sa/4.0/', $attribution->licenseUrl);
    }

    /**
     * Custom licences come from the metadata, and files without creator or licence have no attribution
     *
     * @return void
     */
    public function testCustomAndMissingLicences(): void
    {
        $custom = Attribution::fromMetadata([
            'tx_twbase_license' => 'custom',
            'tx_twbase_license_name' => 'Press photo',
            'tx_twbase_license_url' => 'https://example.org/press',
            'tx_twbase_creation_year' => 0,
            'creator' => [],
        ]);

        self::assertNotNull($custom);
        self::assertSame('', $custom->creator);
        self::assertNull($custom->year);
        self::assertSame('Press photo', $custom->licenseName);
        self::assertSame('https://example.org/press', $custom->licenseUrl);

        $creatorOnly = Attribution::fromMetadata(['creator' => 'Grace Hopper']);
        self::assertNotNull($creatorOnly);
        self::assertNull($creatorOnly->license);
        self::assertSame('', $creatorOnly->licenseName);
        self::assertNull(Attribution::fromMetadata(['tx_twbase_license' => 'unknown']));
    }

    /**
     * Every licence has a name, except a custom one
     *
     * @return void
     */
    public function testLicenceNamesAndTexts(): void
    {
        self::assertSame('All rights reserved', License::AllRightsReserved->title());
        self::assertNull(License::AllRightsReserved->url());
        self::assertSame('CC0 1.0', License::Cc0->title());
        self::assertSame('https://creativecommons.org/publicdomain/zero/1.0/', License::Cc0->url());
        self::assertNull(License::Custom->title());
        self::assertNull(License::Custom->url());
        self::assertSame('CC BY 4.0', License::By->title());
    }
}
