<?php

namespace Tollwerk\TwBase\Domain\Model;

use SJBR\StaticInfoTables\Domain\Model\Country as StaticCountry;

if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
    /**
     * Country
     *
     * @package    Tollwerk\TwBase
     * @subpackage Tollwerk\TwBase\Domain\Model
     */
    class Country extends StaticCountry
    {

    }
}
