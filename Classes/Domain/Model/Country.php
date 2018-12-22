<?php

namespace Tollwerk\TwBase\Domain\Model;

use SJBR\StaticInfoTables\Domain\Model\Country as StaticCountry;

if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
    class Country extends StaticCountry
    {

    }
}
