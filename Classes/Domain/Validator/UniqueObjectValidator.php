<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Validator
 * @author     Klaus Fiedler <klaus@tollwerk.de>
 * @copyright  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
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

namespace Tollwerk\TwBase\Domain\Validator;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks if there already is existing an object nside a given repository
 *
 * @package Tollwerk\TwBase\Domain\Validator
 */
class UniqueObjectValidator extends AbstractValidator
{
    /**
     * Supported options
     *
     * @var array
     */
    protected $supportedOptions = [
        'table'     => [null, 'The table', 'string'],
        'fieldname' => [null, 'The table field name', 'string'],
    ];

    /**
     * /**
     * Check if $value is valid. If it is not valid, needs to add an error to result.
     *
     * @param mixed $value
     */
    public function isValid($value)
    {
        $connection   = GeneralUtility::makeInstance(ConnectionPool::class)
                                      ->getConnectionForTable($this->options['table']);
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder->select('uid')
                               ->from($this->options['table'])
                               ->where($queryBuilder->expr()->eq($this->options['fieldname'],
                                   $queryBuilder->createNamedParameter($value)))
                               ->andWhere($queryBuilder->expr()->eq('deleted', 0))
                               ->setMaxResults(1)
                               ->execute();

        if ($result->rowCount()) {
            $this->result->addError(
                new Error(
                    LocalizationUtility::translate('validator.uniqueObject.error', 'TwBase'),
                    1547075816
                )
            );
        }
    }
}
