<?php

/**
 * Take over the attribution of files from tw_base 4.x
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Upgrades;

use Doctrine\DBAL\Exception as DBALException;
use Override;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Upgrades\ChattyInterface;
use TYPO3\CMS\Core\Upgrades\RepeatableInterface;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;

/**
 * Take over the attribution of files from tw_base 4.x
 *
 * 4.x kept the name, the address and the source of the creator of a file in columns of its own; tw_base now uses
 * the fields "creator" and "source" of EXT:filemetadata and "tx_twbase_creator_url". The wizard copies the old values
 * where the new field is empty, in every language. 4.x also allowed NULL in the columns of the year and the licence,
 * which the database comparison cannot turn into NOT NULL, so the wizard replaces NULL with 0 and an empty string.
 *
 * It runs before the database comparison, as the comparison fails while NULL is left, and therefore has no
 * prerequisites. It runs again after the comparison, which adds "tx_twbase_creator_url", and says so.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[UpgradeWizard('twBase_attributionFrom4x')]
final class AttributionFrom4xUpgradeWizard implements UpgradeWizardInterface, RepeatableInterface, ChattyInterface
{
    /**
     * Table of the metadata of files
     *
     * @var string
     */
    private const string TABLE = 'sys_file_metadata';

    /**
     * Columns that 4.x allowed to be NULL, with the value that replaces NULL
     *
     * @var array<string, integer|string>
     */
    private const array NULLS = [
        'tx_twbase_creation_year' => 0,
        'tx_twbase_license' => '',
        'tx_twbase_license_name' => '',
        'tx_twbase_license_url' => '',
    ];

    /**
     * Columns of 4.x and the fields that take over their values
     *
     * @var array<string, string>
     */
    private const array MOVES = [
        'tx_twbase_author' => 'creator',
        'tx_twbase_author_url' => 'tx_twbase_creator_url',
        'tx_twbase_source_url' => 'source',
    ];

    /**
     * Output, for what is left to do
     *
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * Constructor
     *
     * @param ConnectionPool $connectionPool Connections, for the metadata of files
     */
    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        $this->output = new NullOutput();
    }

    /**
     * Title
     *
     * @return string
     */
    #[Override]
    public function getTitle(): string
    {
        return 'Take over the attribution of files from tw_base 4.x';
    }

    /**
     * Description
     *
     * @return string
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Replaces NULL in the year and the licence of files, which the database comparison cannot change, and '
            . 'copies the creator, its address and the source from the columns of tw_base 4.x where the new fields are '
            . 'empty. Run it before the database comparison and again after it.';
    }

    /**
     * Use the output of the command
     *
     * @param OutputInterface $output Output
     *
     * @return void
     */
    #[Override]
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Whether rows have NULL or old values that the new fields lack
     *
     * @return boolean
     *
     * @throws DBALException If the table cannot be read
     */
    #[Override]
    public function updateNecessary(): bool
    {
        $columns = $this->columns();
        foreach (array_keys(self::NULLS) as $column) {
            if (isset($columns[$column]) && $this->count($column, null) > 0) {
                return true;
            }
        }

        return array_any(
            self::MOVES,
            fn(string $new, string $old): bool => isset($columns[$old])
                && ($this->count($old, isset($columns[$new]) ? $new : '') > 0),
        );
    }

    /**
     * No prerequisites, as the database comparison needs this wizard first
     *
     * @return list<class-string>
     */
    #[Override]
    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * Replace NULL and copy the old values
     *
     * @return boolean
     *
     * @throws DBALException If the table cannot be changed
     */
    #[Override]
    public function executeUpdate(): bool
    {
        $columns = $this->columns();
        foreach (self::NULLS as $column => $value) {
            if (!isset($columns[$column])) {
                continue;
            }

            $query = $this->query();
            $query->update(self::TABLE)
                ->set($column, $value)
                ->where($query->expr()->isNull($column))
                ->executeStatement();
        }

        foreach (self::MOVES as $old => $new) {
            if (!isset($columns[$old])) {
                continue;
            }

            if (!isset($columns[$new])) {
                $this->output->writeln(\sprintf(
                    'The column %s is missing: run the database comparison, then this wizard again for %s.',
                    $new,
                    $old,
                ));
                continue;
            }

            $query = $this->query();
            $query->update(self::TABLE)
                ->set($new, $query->quoteIdentifier($old), false)
                ->where(...$this->pending($query, $old, $new))
                ->executeStatement();
        }

        return true;
    }

    /**
     * Number of rows with NULL in a column, or with an old value that the new field lacks
     *
     * @param string      $column Column with NULL, or the old column
     * @param string|null $new    New field, an empty string if it does not exist, or NULL to count NULL
     *
     * @return integer
     *
     * @throws DBALException If the table cannot be read
     */
    private function count(string $column, ?string $new): int
    {
        $query = $this->query();
        $query->count('uid')->from(self::TABLE);
        if (null === $new) {
            $query->where($query->expr()->isNull($column));
        } elseif ('' === $new) {
            $query->where($query->expr()->isNotNull($column), $query->expr()->neq($column, $query->quote('')));
        } else {
            $query->where(...$this->pending($query, $column, $new));
        }

        $count = $query->executeQuery()->fetchOne();

        return \is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Conditions for a row whose old value the new field lacks
     *
     * @param QueryBuilder $query Query
     * @param string       $old   Old column
     * @param string       $new   New field
     *
     * @return list<string|CompositeExpression>
     */
    private function pending(QueryBuilder $query, string $old, string $new): array
    {
        return [
            $query->expr()->isNotNull($old),
            $query->expr()->neq($old, $query->quote('')),
            $query->expr()->or($query->expr()->isNull($new), $query->expr()->eq($new, $query->quote(''))),
        ];
    }

    /**
     * Columns of the table
     *
     * @return array<string, true> Names in lower case
     *
     * @throws DBALException If the table cannot be read
     */
    private function columns(): array
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $columns = [];
        foreach ($connection->createSchemaManager()->introspectTableColumnsByUnquotedName(self::TABLE) as $column) {
            $columns[strtolower($column->getObjectName()->getIdentifier()->getValue())] = true;
        }

        return $columns;
    }

    /**
     * Query without restrictions, as every row counts, also deleted and hidden ones
     *
     * @return QueryBuilder
     */
    private function query(): QueryBuilder
    {
        $query = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $query->getRestrictions()->removeAll();

        return $query;
    }
}
