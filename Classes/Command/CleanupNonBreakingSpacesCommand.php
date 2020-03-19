<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Command
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

namespace Tollwerk\TwBase\Command;

use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cleanup non breaking spaces in RTE fields
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Command
 */
class CleanupNonBreakingSpacesCommand extends Command
{
    /**
     * Command name
     *
     * @var string
     */
    protected $name = 'cleanup:nbsp';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Replace non-breaking spaces in text fields');
        $this->setHelp('Replaces all non-breaking spaces in RTE fields with regular spaces');
    }

    /**
     * Executes the command for cleaning processed files
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int Status
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Run through all cleanup tables
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nbspCleanup'] as $table => $fields) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
            foreach ((array)$fields as $field) {
                $field = trim($field);
                if (strlen($field)) {
                    $qField = $connection->quoteIdentifier($field);
                    $qTable = $connection->quoteIdentifier($table);
//                    $query  = 'SELECT '.$qField.' FROM '.$qTable.' WHERE '.$qField.' LIKE "%&nbsp;%"';
                    $query  = 'UPDATE '.$qTable.' SET '.$qField.' = REPLACE('.$qField.', "&nbsp;", " ") WHERE '.$qField.' LIKE "%&nbsp;%"';
                    $result = $connection->executeQuery($query);
                }
            }
        }

        return 0;
    }
}
