<?php

namespace Tollwerk\TwBase\Domain\Repository\Traits;

use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Trait to ignore storage PIDs
 *
 * @package  Tollwerk\TwBase
 * @property ObjectManagerInterface $objectManager  Object Manager
 */
trait StoragePidsIgnoringRepositoryTrait
{
    /**
     * Initializes the repository.
     */
    public function initializeObject()
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
