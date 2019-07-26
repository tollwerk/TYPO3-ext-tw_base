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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tollwerk\TwBase\Service\ImageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Cleanup converted files
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Command
 */
class CleanupConvertedFilesCommand extends Command
{
    /**
     * Processed files repository
     *
     * @var ProcessedFileRepository
     */
    protected $processedFileRepository;
    /**
     * Command name
     *
     * @var string
     */
    protected $name = 'cleanup:convertedfiles';

    /**
     * Constructor
     *
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $objectManager                 = GeneralUtility::makeInstance(ObjectManager::class);
        $this->processedFileRepository = $objectManager->get(ProcessedFileRepository::class);
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Clear all converted files');
        $this->setHelp('Removes converted file variants from the database and the filesystem');
    }

    /**
     * Executes the command for cleaning processed files
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryBuilder    = GeneralUtility::makeInstance(ConnectionPool::class)
                                         ->getQueryBuilderForTable('sys_file_processedfile');
        $result          = $queryBuilder
            ->select('original')->from('sys_file_processedfile')
            ->where($queryBuilder->expr()->neq('identifier', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)))
            ->andWhere(
                $queryBuilder->expr()->eq('task_type',
                    $queryBuilder->createNamedParameter(ImageService::CONTEXT_CONVERT, \PDO::PARAM_STR)
                )
            )->execute();
        $resourceFactory = ResourceFactory::getInstance();
        while ($row = $result->fetch()) {
            try {
                $originalFile = $resourceFactory->getFileObject($row['original']);
                foreach ($this->processedFileRepository->findAllByOriginalFile($originalFile) as $processedFile) {
                    if ($processedFile->getTaskIdentifier() == ImageService::CONTEXT_CONVERT) {
                        $processedFile->getStorage()->setEvaluatePermissions(false);
                        $processedFile->delete(true);
                    }
                }
            } catch (\Exception $e) {
                $output->writeln(
                    sprintf(
                        '<error>Failed to delete file "%s" (%s: %s)</error>',
                        $row['identifier'],
                        get_class($e),
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
