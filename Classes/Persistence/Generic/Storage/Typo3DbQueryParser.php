<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Persistence\Generic\Storage
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2021 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Tollwerk\TwBase\Persistence\Generic\Storage;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Custom TYPO3 DB Query Parser
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Persistence\Generic\Storage
 * @see        https://forge.typo3.org/issues/90013
 */
class Typo3DbQueryParser extends \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser
{
    /**
     * Mappings from TCA enable fields to query restrictions
     *
     * @var QueryRestrictionInterface[]
     */
    protected static $enableFieldsRestrictionMappings = [
        'delete'    => DeletedRestriction::class,
        'disabled'  => HiddenRestriction::class,
        'starttime' => StartTimeRestriction::class,
        'endtime'   => EndTimeRestriction::class,
        'fe_group'  => FrontendGroupRestriction::class,
    ];

    /**
     * Returns a ready to be executed QueryBuilder object, based on the query
     *
     * @param QueryInterface $query
     *
     * @return QueryBuilder
     */
    public function convertQueryToDoctrineQueryBuilder(QueryInterface $query)
    {
        // Reset all properties
        $this->tablePropertyMap     = [];
        $this->tableAliasMap        = [];
        $this->unionTableAliasCache = [];
        $this->tableName            = '';

        if ($query->getStatement() && $query->getStatement()->getStatement() instanceof QueryBuilder) {
            $this->queryBuilder = clone $query->getStatement()->getStatement();

            return $this->queryBuilder;
        }
        // Find the right table name
        $source = $query->getSource();
        $this->initializeQueryBuilderWithInheritedRestrictions($query, $source);

        $constraint = $query->getConstraint();
        if ($constraint instanceof ConstraintInterface) {
            $wherePredicates = $this->parseConstraint($constraint, $source);
            if (!empty($wherePredicates)) {
                $this->queryBuilder->andWhere($wherePredicates);
            }
        }

        $this->parseOrderings($query->getOrderings(), $source);
        $this->addTypo3Constraints($query);

        return $this->queryBuilder;
    }

    /**
     * Creates the queryBuilder object whether it is a regular select or a JOIN
     *
     * @param QueryInterface  $query  Query
     * @param SourceInterface $source The source
     *
     * @throws Exception
     */
    protected function initializeQueryBuilderWithInheritedRestrictions(QueryInterface $query, SourceInterface $source)
    {
        if ($source instanceof SelectorInterface) {
            $className       = $source->getNodeTypeName();
            $tableName       = $this->dataMapper->getDataMap($className)->getTableName();
            $this->tableName = $tableName;

            $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                                ->getQueryBuilderForTable($tableName);
            $this->queryBuilder->getRestrictions()->removeAll();

            $tableAlias = $this->getUniqueAlias($tableName);

            $this->queryBuilder
                ->select($tableAlias . '.*')
                ->from($tableName, $tableAlias);

            $this->addRecordTypeConstraint($className);
        } elseif ($source instanceof JoinInterface) {
            $leftSource    = $source->getLeft();
            $leftTableName = $leftSource->getSelectorName();

            $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                                ->getQueryBuilderForTable($leftTableName);

            // Inherit restrictions from base query
            if ($query->getQuerySettings()->getIgnoreEnableFields()) {
                if (!empty($ignoreEnableFields = $query->getQuerySettings()->getEnableFieldsToBeIgnored())) {
                    foreach ($ignoreEnableFields as $ignoreEnableField) {
                        if (!empty(static::$enableFieldsRestrictionMappings[$ignoreEnableField])) {
                            $this->queryBuilder->getRestrictions()
                                               ->removeByType(static::$enableFieldsRestrictionMappings[$ignoreEnableField]);
                        }
                    }
                } else {
                    $this->queryBuilder->getRestrictions()->removeAll();
                }
            }

            $leftTableAlias = $this->getUniqueAlias($leftTableName);
            $this->queryBuilder
                ->select($leftTableAlias . '.*')
                ->from($leftTableName, $leftTableAlias);
            $this->parseJoin($source, $leftTableAlias);
        }
    }
}
