<?php
/**
 * Created by PhpStorm.
 * User: lucifer
 * Date: 10.01.2019
 * Time: 00:05
 */

namespace Tollwerk\TwBase\Domain\Validator;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks if there already is existing an object
 * inside a given repository
 *
 * @package Tollwerk\TwBase\Domain\Validator
 */
class UniqueObjectValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'table' => [null, 'The table', 'string'],
        'fieldname' => [null, 'The table field name', 'string'],
    ];

    public function isValid($value)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->options['table']);
        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder->select('uid')
            ->from($this->options['table'])
            ->where($queryBuilder->expr()->eq($this->options['fieldname'], $queryBuilder->createNamedParameter($value)))
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
