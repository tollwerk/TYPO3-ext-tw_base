<?php

/**
 * Tests of the wizard that takes over the attribution of files from tw_base 4.x
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

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Types;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Tollwerk\TwBase\Upgrades\AttributionFrom4xUpgradeWizard;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests of the wizard that takes over the attribution of files from tw_base 4.x
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class AttributionFrom4xUpgradeWizardTest extends FunctionalTestCase
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
     * Old values move where the new fields are empty, and NULL becomes 0 or an empty string
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws DBALException If the table cannot be changed
     */
    #[Test]
    public function oldValuesMoveAndNullIsReplaced(): void
    {
        $this->useSchemaOf4x(true);
        $connection = $this->connection();
        $connection->insert('sys_file_metadata', [
            'uid' => 1,
            'tx_twbase_author' => 'Jane Doe',
            'tx_twbase_author_url' => 'https://example.org/jane',
            'tx_twbase_source_url' => 'https://example.org/photo',
            'tx_twbase_creation_year' => null,
            'tx_twbase_license' => 'BY',
            'tx_twbase_license_name' => null,
            'tx_twbase_license_url' => null,
        ]);
        $connection->insert('sys_file_metadata', [
            'uid' => 2,
            'creator' => 'Kept',
            'tx_twbase_author' => 'Replaced?',
            'tx_twbase_creation_year' => 2020,
            'tx_twbase_license' => '',
            'tx_twbase_license_name' => '',
            'tx_twbase_license_url' => '',
        ]);
        $wizard = $this->get(AttributionFrom4xUpgradeWizard::class);

        self::assertTrue($wizard->updateNecessary());
        self::assertTrue($wizard->executeUpdate());
        self::assertFalse($wizard->updateNecessary());
        self::assertSame([
            'creator' => 'Jane Doe',
            'tx_twbase_creator_url' => 'https://example.org/jane',
            'source' => 'https://example.org/photo',
            'tx_twbase_creation_year' => 0,
            'tx_twbase_license_name' => '',
            'tx_twbase_license_url' => '',
        ], $this->row(1));
        self::assertSame('Kept', $this->row(2)['creator'] ?? null);
    }

    /**
     * Before the database comparison adds the address of the creator, the wizard says to run again, and does
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws DBALException If the table cannot be changed
     */
    #[Test]
    public function wizardRunsAgainAfterTheComparison(): void
    {
        $this->useSchemaOf4x(false);
        $this->connection()->insert('sys_file_metadata', [
            'uid' => 1,
            'tx_twbase_author' => 'Jane Doe',
            'tx_twbase_author_url' => 'https://example.org/jane',
        ]);
        $wizard = $this->get(AttributionFrom4xUpgradeWizard::class);
        $output = new BufferedOutput();
        $wizard->setOutput($output);

        self::assertTrue($wizard->executeUpdate());
        self::assertStringContainsString('run the database comparison, then this wizard again', $output->fetch());
        self::assertTrue($wizard->updateNecessary());
        self::assertSame('Jane Doe', $this->connection()->select(['creator'], 'sys_file_metadata')->fetchOne());
    }

    /**
     * Turn the table into that of tw_base 4.x: the old columns, NULL allowed, perhaps no address of the creator yet
     *
     * @param boolean $compared Whether the database comparison added the address of the creator already
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws DBALException If the table cannot be changed
     */
    private function useSchemaOf4x(bool $compared): void
    {
        $connection = $this->connection();
        $schema = $connection->createSchemaManager();
        $current = $schema->introspectTableByUnquotedName('sys_file_metadata');
        $old = clone $current;
        foreach (['tx_twbase_author', 'tx_twbase_author_url', 'tx_twbase_source_url'] as $column) {
            $old->addColumn($column, Types::TEXT, ['notnull' => false]);
        }

        foreach (['tx_twbase_creation_year', 'tx_twbase_license_name', 'tx_twbase_license_url'] as $column) {
            $old->getColumn($column)->setNotnull(false)->setDefault(null);
        }

        if (!$compared) {
            $old->dropColumn('tx_twbase_creator_url');
        }

        $schema->alterTable($schema->createComparator()->compareTables($current, $old));
    }

    /**
     * Fields of the attribution of a row
     *
     * @param integer $uid Uid
     *
     * @return array<string, mixed>
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws DBALException If the table cannot be read
     */
    private function row(int $uid): array
    {
        $row = $this->connection()->select(
            ['creator', 'tx_twbase_creator_url', 'source', 'tx_twbase_creation_year', 'tx_twbase_license_name',
                'tx_twbase_license_url'],
            'sys_file_metadata',
            ['uid' => $uid],
        )->fetchAssociative();
        self::assertIsArray($row);
        $year = $row['tx_twbase_creation_year'] ?? null;
        $row['tx_twbase_creation_year'] = \is_numeric($year) ? (int) $year : $year;

        return $row;
    }

    /**
     * Connection of the metadata of files
     *
     * @return Connection
     *
     * @throws ContainerExceptionInterface If a service is missing
     */
    private function connection(): Connection
    {
        return $this->get(ConnectionPool::class)->getConnectionForTable('sys_file_metadata');
    }
}
