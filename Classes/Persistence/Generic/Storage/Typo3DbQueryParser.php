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
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
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

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
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
        $queryBuilder = parent::convertQueryToDoctrineQueryBuilder($query);

        if ($query->getQuerySettings()->getIgnoreEnableFields()) {
            if (!empty($ignoreEnableFields = $query->getQuerySettings()->getEnableFieldsToBeIgnored())) {
                foreach ($ignoreEnableFields as $ignoreEnableField) {
                    if (!empty(static::$enableFieldsRestrictionMappings[$ignoreEnableField])) {
                        $queryBuilder->getRestrictions()
                                     ->removeByType(static::$enableFieldsRestrictionMappings[$ignoreEnableField]);
                    }
                }
            } else {
                $queryBuilder->getRestrictions()->removeAll();
            }
        }

        return $queryBuilder;
    }
}
