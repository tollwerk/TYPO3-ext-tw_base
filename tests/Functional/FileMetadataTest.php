<?php

/**
 * Tests of the fields of the metadata of files
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Functional;

use Override;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests of the fields of the metadata of files
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class FileMetadataTest extends FunctionalTestCase
{
    /**
     * Extensions under test
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $testExtensionsToLoad = ['tollwerk/tw-base'];

    /**
     * Extensions of the core that tw_base needs
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $coreExtensionsToLoad = ['filemetadata'];

    /**
     * The attribution is the same in every language, like the creator, except the name of a custom licence
     *
     * @return void
     */
    #[Test]
    public function attributionIsTheSameInEveryLanguage(): void
    {
        foreach (['creator', 'tx_twbase_creator_url', 'tx_twbase_creation_year', 'tx_twbase_license',
            'tx_twbase_license_url'] as $column) {
            self::assertSame('exclude', $this->value('columns', $column, 'l10n_mode'), $column);
        }

        self::assertNull($this->value('columns', 'tx_twbase_license_name', 'l10n_mode'));
    }

    /**
     * Images and videos need an alternative text
     *
     * @return void
     */
    #[Test]
    public function imagesAndVideosNeedAnAlternativeText(): void
    {
        self::assertTrue($this->value('columns', 'alternative', 'config', 'required'));
        foreach (['20', '25'] as $palette) {
            $fields = $this->value('palettes', $palette, 'showitem');
            self::assertIsString($fields);
            self::assertStringContainsString('alternative', $fields);
        }
    }

    /**
     * Value in the TCA of the metadata
     *
     * @param string ...$path Keys below the table
     *
     * @return mixed Value, or NULL if it is missing
     */
    private function value(string ...$path): mixed
    {
        $value = $GLOBALS['TCA'] ?? null;
        foreach (['sys_file_metadata', ...$path] as $key) {
            if (!\is_array($value)) {
                return null;
            }

            $value = $value[$key] ?? null;
        }

        return $value;
    }
}
