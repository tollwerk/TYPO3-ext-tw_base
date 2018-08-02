<?php

namespace Tollwerk\TwBase\Command;

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
}