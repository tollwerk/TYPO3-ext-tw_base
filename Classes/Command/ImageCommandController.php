<?php

namespace Tollwerk\TwBase\Command;

use Doctrine\DBAL\FetchMode;
use Tollwerk\TwBase\Service\ImageService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Converter Command Controller
 */
class ImageCommandController extends CommandController
{
    /**
     * Processed file repository
     *
     * @var ProcessedFileRepository
     */
    protected $processedFileRepository;
    /**
     * Processed file table
     *
     * @var string
     */
    protected $table = 'sys_file_processedfile';

    /**
     * Inject the processed file repository
     *
     * @param ProcessedFileRepository $processedFileRepository
     */
    public function injectProcessedFileRepository(ProcessedFileRepository $processedFileRepository): void
    {
        $this->processedFileRepository = $processedFileRepository;
    }

    /**
     * Clear converted image variants from the database and filesystem
     */
    public function clearConvertedCommand()
    {
        $this->clearProcessedFiles(ImageService::CONTEXT_CONVERT);
    }

    /**
     * Clear processed images by task type
     *
     * @param string $taskType Task type
     */
    protected function clearProcessedFiles(string $taskType): void
    {
        $queryBuilder    = GeneralUtility::makeInstance(ConnectionPool::class)
                                         ->getQueryBuilderForTable('sys_file_processedfile');
        $result          = $queryBuilder->select('original')->from($this->table)
                                        ->where(
                                            $queryBuilder->expr()->neq(
                                                'identifier',
                                                $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                                            )
                                        )->andWhere(
                $queryBuilder->expr()->eq(
                    'task_type',
                    $queryBuilder->createNamedParameter($taskType, \PDO::PARAM_STR)
                )
            )->execute();
        $errorCount      = 0;
        $resourceFactory = ResourceFactory::getInstance();

        while ($row = $result->fetch()) {
            try {
                $originalFile = $resourceFactory->getFileObject($row['original']);
                foreach ($this->processedFileRepository->findAllByOriginalFile($originalFile) as $processedFile) {
                    if ($processedFile->getTaskIdentifier() == $taskType) {
                        $processedFile->getStorage()->setEvaluatePermissions(false);
                        $processedFile->delete(true);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error(
                    'Failed to delete file "'.$row['identifier'].'".',
                    [
                        'exception' => $e
                    ]
                );
                ++$errorCount;
            }
        }
    }

    /**
     * Clear all processed files
     */
    public function clearProcessedCommand()
    {
        $this->processedFileRepository->removeAll();
    }

    /**
     * Clear orphan file references
     */
    public function clearOrphanFilereferencesCommand()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        // Get a list of all available tables
        $connection = $connectionPool->getConnectionForTable('sys_file_reference');
        $query      = $connection->query('SHOW TABLES');
        $tablenames = $query->execute() ? $query->fetchAll(FetchMode::COLUMN) : [];

        // Delete all file references with invalid record IDs or empty tablenames
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder->delete('sys_file_reference')
                              ->where($queryBuilder->expr()->eq('uid_foreign', 0))
                              ->orWhere($queryBuilder->expr()->eq('tablenames', $queryBuilder->quote('')));
        if (count($tablenames)) {
            $query->orWhere($queryBuilder->expr()->notIn(
                'tablenames',
                array_map([$queryBuilder, 'quote'], $tablenames))
            );
        }
        $query->execute();

        // Extract all referenced tables
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        $query  = $queryBuilder->select('tablenames')
                               ->from('sys_file_reference')
                               ->groupBy('tablenames');
        $result = $query->execute();
        if ($result) {
            // Run through all join tables
            foreach ($result->fetchAll(FetchMode::COLUMN) as $joinTable) {
                $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference');
                $queryBuilder->getRestrictions()->removeAll();
                $orphanQuery  = $queryBuilder->select('s.uid AS local', 'j.uid AS foreign')
                                             ->from('sys_file_reference', 's')
                                             ->leftJoin(
                                                 's',
                                                 $joinTable,
                                                 'j',
                                                 $queryBuilder->expr()->eq(
                                                     's.uid_foreign',
                                                     $queryBuilder->quoteIdentifier('j.uid')
                                                 )
                                             )
                                             ->where($queryBuilder->expr()->eq(
                                                 's.tablenames',
                                                 $queryBuilder->quote($joinTable))
                                             )
                                             ->having($queryBuilder->expr()->isNull('foreign'));
                $orphanResult = $orphanQuery->execute();
                if ($orphanResult) {
                    $orphans = $orphanResult->fetchAll(FetchMode::COLUMN);
                    if (count($orphans)) {
                        $orphanQueryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference');
                        $orphanQueryBuilder->getRestrictions()->removeAll();
                        $orphanDeleteQuery = $orphanQueryBuilder->delete('sys_file_reference')
                                                                ->where(
                                                                    $orphanQueryBuilder->expr()->in(
                                                                        'uid',
                                                                        array_map(
                                                                            [$orphanQueryBuilder, 'quote'],
                                                                            $orphans
                                                                        )
                                                                    ));
                        $orphanDeleteQuery->execute();
                    }
                }
            }
        }
    }
}
