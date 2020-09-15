<?php
/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Command
 * @author     Klaus Fiedler <klaus@tollwerk.de>
 * @copyright  Copyright © 2020 Klaus Fiedler <klaus@tollwerk.de>
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2020 Klaus Fiedler <klaus@tollwerk.de>
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


use Safe\Exceptions\MysqlException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UpdateSlugsCommand
 *
 * Generate slugs for all records of [tablename].[slugFieldName]
 *
 * @package Tollwerk\TwBase\Command
 */
class UpdateSlugsCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    protected $io = null;

    /**
     * Name of the table
     *
     * @var string
     */
    protected $table = '';

    /**
     * Names of the slug fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * If true, slugs will be updated even if they are already set
     *
     * @var bool
     */
    protected $forceUpdate = false;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Update slugs for all records')
            ->addArgument(
                'table',
                InputOption::VALUE_REQUIRED,
                'The tablename like \'pages\' or \'tx_myextension_domain_model_myrecord\''
            )
            ->addArgument(
                'fields',
                InputOption::VALUE_REQUIRED,
                'One or multiple, comma separated, fieldnames, like \'slug\' or \'"slug|another_slug, yet_another_field"\''
            )
            ->addOption(
                'force-update',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Force update of existing slug values.',
                false
            )
            ->setHelp('Finds all records of [tablename] and updates their [fieldname] slug field (if empty) by using the TCA configuration for [fieldname], assuming it\'s type is configured as \'slug\'.');;
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // Instantiate SymfonyStyle object for input/output
        $this->io = new SymfonyStyle($input, $output);

        // Get arguments
        $this->table = $input->getArgument('table');
        $this->fields = array_filter(GeneralUtility::trimExplode(',', $input->getArgument('fields')));
        $this->forceUpdate = ($input->getOption('force-update') !== false);
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        if(!$this->table || !count($this->fields)) {
            throw new \Exception('No tablename or fieldname given. If you are using -v together with other arguments like -f, please make sure that -v always comes last.');
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        $records = $queryBuilder->select('*')->from($this->table)->execute()->fetchAll();
        if ($this->io->isVerbose()) {
            $this->io->text(count($records) ? sprintf('%s records found.', count($records)) : 'No records found');
            $this->io->newLine();
        }
        return $records;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->io->isVerbose()) {
            $this->io->title($this->getDescription());
            $this->io->text('tablename: ' . $this->table);
            $this->io->text('fields: ' . implode(', ', $this->fields));
            $this->io->newLine();
        }

        // Get records
        $records = $this->getRecords();
        $skippedSlugs = 0;
        $updatedSlugs = 0;
        if (!count($records)) {
            return 1;
        }

        // Create slugs and update records
        foreach ($records as $record) {
            // Create slug for each field
            $slugs = [];
            foreach ($this->fields as $field) {

                // Skip this field if a slug is already set
                if(!empty($record[$field]) && !$this->forceUpdate) {
                    $skippedSlugs++;
                    continue;
                }

                // Get SlugHelper for current field
                /** @var SlugHelper slugHelper */
                $slugHelper = GeneralUtility::makeInstance(
                    SlugHelper::class,
                    $this->table,
                    $field,
                    $GLOBALS['TCA'][$this->table]['columns'][$field]['config']
                );

                // Generate slug and add it to $slugs array for database update
                $slugs[$field] = $slugHelper->generate($record, intval($record['pid']) ?: 1);

                if ($this->io->isVerbose()) {
                    $this->io->text(sprintf('[%s] %s.%s: %s',
                        $record['uid'],
                        $this->table,
                        $field,
                        $slugs[$field]
                    ));
                }
            }

            // Update the record, setting all slugs
            if(count($slugs)) {
                /** @var ConnectionPool $connection */
                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->table);
                $connectionPool->update(
                    $this->table,
                    $slugs,
                    ['uid' => $record['uid']],
                    [Connection::PARAM_STR]
                );
                $updatedSlugs++;
            }
        }

        if($this->io->isVerbose()) {
            $this->io->newLine();
            $this->io->text(sprintf('Updated %s slugs.', $updatedSlugs));
            $this->io->text(sprintf('Skipped %s slugs.', $skippedSlugs));
            $this->io->newLine();
            $this->io->text('Done.');
            $this->io->newLine();
        }

        return 0;
    }

}
