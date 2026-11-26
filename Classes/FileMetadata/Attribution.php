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

namespace Tollwerk\TwBase\FileMetadata;

/**
 * Who made a file and under which licence it may be used
 *
 * Creator and source are the fields of the core extension filemetadata; tw_base adds the address of the creator, the
 * year and the licence.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class Attribution
{
    /**
     * Constructor
     *
     * @param string       $creator     Creator
     * @param string       $creatorUrl  Address of the creator
     * @param string       $source      Address of the source
     * @param integer|null $year        Year of creation
     * @param License|null $license     Licence
     * @param string       $licenseName Name of the licence
     * @param string       $licenseUrl  Address of the licence text
     */
    public function __construct(
        public string $creator,
        public string $creatorUrl,
        public string $source,
        public ?int $year,
        public ?License $license,
        public string $licenseName,
        public string $licenseUrl,
    ) {}

    /**
     * Attribution from the metadata of a file
     *
     * @param array<mixed> $metadata Metadata, as from sys_file_metadata
     *
     * @return self|null Attribution, or NULL if the metadata names neither creator nor licence
     */
    public static function fromMetadata(array $metadata): ?self
    {
        $license = License::tryFrom(self::string($metadata, 'tx_twbase_license'));
        $creator = self::string($metadata, 'creator');
        if (($creator === '') && ($license === null)) {
            return null;
        }

        $year = self::string($metadata, 'tx_twbase_creation_year');
        $custom = $license === License::Custom;

        return new self(
            $creator,
            self::string($metadata, 'tx_twbase_creator_url'),
            self::string($metadata, 'source'),
            ((int) $year > 0) ? (int) $year : null,
            $license,
            $custom ? self::string($metadata, 'tx_twbase_license_name') : ($license?->title() ?? ''),
            $custom ? self::string($metadata, 'tx_twbase_license_url') : ($license?->url() ?? ''),
        );
    }

    /**
     * String value of the metadata
     *
     * @param array<mixed> $metadata Metadata
     * @param string       $key      Key
     *
     * @return string
     */
    private static function string(array $metadata, string $key): string
    {
        $value = $metadata[$key] ?? '';

        return \is_scalar($value) ? trim((string) $value) : '';
    }
}
