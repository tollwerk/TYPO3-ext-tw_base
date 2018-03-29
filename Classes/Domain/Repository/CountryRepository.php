<?php

namespace Tollwerk\TwBase\Domain\Repository;

use SJBR\StaticInfoTables\Domain\Repository\CountryRepository as StaticCountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
    /**
     * Country repository
     */
    class CountryRepository extends StaticCountryRepository implements RepositoryInterface
    {
        /**
         * Find a list of countries by an international phone number
         *
         * @param int $intlPhoneNumer International phone number
         *
         * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface Matching countries
         */
        public function findByIntlPhoneNumber($intlPhoneNumer)
        {
            $intlPhoneNumer = preg_replace('/[^0-9]+/', '', $intlPhoneNumer);
            if ($intlPhoneNumer[0] === '0') {
                return null;
            }
            $sql   = 'SELECT * FROM `static_countries` WHERE "'.intval($intlPhoneNumer)
                     .'" LIKE CONCAT(`cn_phone`, "%") ORDER BY LENGTH(`cn_phone`) DESC';
            $query = $this->createQuery();
            $query->statement($sql);

            return $query->execute();
        }
    }
} else {
    /**
     * Country repository stub
     */
    class CountryRepository
    {
        /**
         * Find a list of countries by an international phone number
         *
         * @param int $intlPhoneNumer International phone number
         *
         * @return null Matching countries
         */
        public function findByIntlPhoneNumber($intlPhoneNumer)
        {
            return null;
        }
    }
}