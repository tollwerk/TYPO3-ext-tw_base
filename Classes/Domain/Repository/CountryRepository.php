<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Domain\Repository
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
                     .'" LIKE CONCAT(`cnPhone`, "%") ORDER BY LENGTH(`cnPhone`) DESC';
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
         * Countries
         *
         * SELECT cnPhone, cnShortLocal, cnShortEn FROM `static_countries` WHERE cnPhone
         *
         * @var array
         */
        protected static $countries = [
            ['cnPhone' => '376', 'cnShortLocal' => 'Andorra', 'cnShortEn' => 'Andorra'],
            [
                'cnPhone'      => '971',
                'cnShortLocal' => 'الإمارات العربيّة المتّحدة',
                'cnShortEn'    => 'United Arab Emirates'
            ],
            ['cnPhone' => '93', 'cnShortLocal' => 'افغانستان', 'cnShortEn' => 'Afghanistan'],
            [
                'cnPhone'      => '1268',
                'cnShortLocal' => 'Antigua and Barbuda',
                'cnShortEn'    => 'Antigua and Barbuda'
            ],
            ['cnPhone' => '1264', 'cnShortLocal' => 'Anguilla', 'cnShortEn' => 'Anguilla'],
            ['cnPhone' => '355', 'cnShortLocal' => 'Shqipëria', 'cnShortEn' => 'Albania'],
            ['cnPhone' => '374', 'cnShortLocal' => 'Հայաստան', 'cnShortEn' => 'Armenia'],
            [
                'cnPhone'      => '599',
                'cnShortLocal' => 'Nederlandse Antillen',
                'cnShortEn'    => 'Netherlands Antilles'
            ],
            ['cnPhone' => '244', 'cnShortLocal' => 'Angola', 'cnShortEn' => 'Angola'],
            ['cnPhone' => '67212', 'cnShortLocal' => 'Antarctica', 'cnShortEn' => 'Antarctica'],
            ['cnPhone' => '54', 'cnShortLocal' => 'Argentina', 'cnShortEn' => 'Argentina'],
            ['cnPhone' => '685', 'cnShortLocal' => 'Amerika Samoa', 'cnShortEn' => 'American Samoa'],
            ['cnPhone' => '43', 'cnShortLocal' => 'Österreich', 'cnShortEn' => 'Austria'],
            ['cnPhone' => '61', 'cnShortLocal' => 'Australia', 'cnShortEn' => 'Australia'],
            ['cnPhone' => '297', 'cnShortLocal' => 'Aruba', 'cnShortEn' => 'Aruba'],
            ['cnPhone' => '994', 'cnShortLocal' => 'Azərbaycan', 'cnShortEn' => 'Azerbaijan'],
            ['cnPhone' => '387', 'cnShortLocal' => 'BiH/БиХ', 'cnShortEn' => 'Bosnia and Herzegovina'],
            ['cnPhone' => '1246', 'cnShortLocal' => 'Barbados', 'cnShortEn' => 'Barbados'],
            ['cnPhone' => '880', 'cnShortLocal' => 'বাংলাদেশ', 'cnShortEn' => 'Bangladesh'],
            ['cnPhone' => '32', 'cnShortLocal' => 'Belgique', 'cnShortEn' => 'Belgium'],
            ['cnPhone' => '226', 'cnShortLocal' => 'Burkina', 'cnShortEn' => 'Burkina Faso'],
            ['cnPhone' => '359', 'cnShortLocal' => 'Bulgaria', 'cnShortEn' => 'Bulgaria'],
            ['cnPhone' => '973', 'cnShortLocal' => 'البحري', 'cnShortEn' => 'Bahrain'],
            ['cnPhone' => '257', 'cnShortLocal' => 'Burundi', 'cnShortEn' => 'Burundi'],
            ['cnPhone' => '229', 'cnShortLocal' => 'Bénin', 'cnShortEn' => 'Benin'],
            ['cnPhone' => '1441', 'cnShortLocal' => 'Bermuda', 'cnShortEn' => 'Bermuda'],
            ['cnPhone' => '673', 'cnShortLocal' => 'دارالسلام', 'cnShortEn' => 'Brunei'],
            ['cnPhone' => '591', 'cnShortLocal' => 'Bolivia', 'cnShortEn' => 'Bolivia'],
            ['cnPhone' => '55', 'cnShortLocal' => 'Brasil', 'cnShortEn' => 'Brazil'],
            ['cnPhone' => '1242', 'cnShortLocal' => 'The Bahamas', 'cnShortEn' => 'The Bahamas'],
            ['cnPhone' => '975', 'cnShortLocal' => 'Druk-Yul', 'cnShortEn' => 'Bhutan'],
            ['cnPhone' => '267', 'cnShortLocal' => 'Botswana', 'cnShortEn' => 'Botswana'],
            ['cnPhone' => '375', 'cnShortLocal' => 'Беларусь', 'cnShortEn' => 'Belarus'],
            ['cnPhone' => '501', 'cnShortLocal' => 'Belize', 'cnShortEn' => 'Belize'],
            ['cnPhone' => '1', 'cnShortLocal' => 'Canada', 'cnShortEn' => 'Canada'],
            [
                'cnPhone'      => '6722',
                'cnShortLocal' => 'Cocos (Keeling) Islands',
                'cnShortEn'    => 'Cocos (Keeling) Islands'
            ],
            ['cnPhone' => '243', 'cnShortLocal' => 'Congo', 'cnShortEn' => 'Congo'],
            [
                'cnPhone'      => '236',
                'cnShortLocal' => 'République centrafricaine',
                'cnShortEn'    => 'Central African Republic'
            ],
            ['cnPhone' => '242', 'cnShortLocal' => 'Congo-Brazzaville', 'cnShortEn' => 'Congo-Brazzaville'],
            ['cnPhone' => '41', 'cnShortLocal' => 'Schweiz', 'cnShortEn' => 'Switzerland'],
            ['cnPhone' => '225', 'cnShortLocal' => 'Côte d’Ivoire', 'cnShortEn' => 'Côte d’Ivoire'],
            ['cnPhone' => '682', 'cnShortLocal' => 'Cook Islands', 'cnShortEn' => 'Cook Islands'],
            ['cnPhone' => '56', 'cnShortLocal' => 'Chile', 'cnShortEn' => 'Chile'],
            ['cnPhone' => '237', 'cnShortLocal' => 'Cameroun', 'cnShortEn' => 'Cameroon'],
            ['cnPhone' => '86', 'cnShortLocal' => '中华', 'cnShortEn' => 'China'],
            ['cnPhone' => '57', 'cnShortLocal' => 'Colombia', 'cnShortEn' => 'Colombia'],
            ['cnPhone' => '506', 'cnShortLocal' => 'Costa Rica', 'cnShortEn' => 'Costa Rica'],
            ['cnPhone' => '53', 'cnShortLocal' => 'Cuba', 'cnShortEn' => 'Cuba'],
            ['cnPhone' => '238', 'cnShortLocal' => 'Cabo Verde', 'cnShortEn' => 'Cape Verde'],
            ['cnPhone' => '6724', 'cnShortLocal' => 'Christmas Island', 'cnShortEn' => 'Christmas Island'],
            ['cnPhone' => '357', 'cnShortLocal' => 'Κύπρος / Kıbrıs', 'cnShortEn' => 'Cyprus'],
            ['cnPhone' => '420', 'cnShortLocal' => 'Česko', 'cnShortEn' => 'Czech Republic'],
            ['cnPhone' => '49', 'cnShortLocal' => 'Deutschland', 'cnShortEn' => 'Germany'],
            ['cnPhone' => '253', 'cnShortLocal' => 'جيبوتي /Djibouti', 'cnShortEn' => 'Djibouti'],
            ['cnPhone' => '45', 'cnShortLocal' => 'Danmark', 'cnShortEn' => 'Denmark'],
            ['cnPhone' => '1767', 'cnShortLocal' => 'Dominica', 'cnShortEn' => 'Dominica'],
            ['cnPhone' => '1809', 'cnShortLocal' => 'Quisqueya', 'cnShortEn' => 'Dominican Republic'],
            ['cnPhone' => '213', 'cnShortLocal' => 'الجزائ', 'cnShortEn' => 'Algeria'],
            ['cnPhone' => '593', 'cnShortLocal' => 'Ecuador', 'cnShortEn' => 'Ecuador'],
            ['cnPhone' => '372', 'cnShortLocal' => 'Eesti', 'cnShortEn' => 'Estonia'],
            ['cnPhone' => '20', 'cnShortLocal' => 'مصر', 'cnShortEn' => 'Egypt'],
            ['cnPhone' => '212', 'cnShortLocal' => 'الصحراء الغربي', 'cnShortEn' => 'Western Sahara'],
            ['cnPhone' => '291', 'cnShortLocal' => 'ኤርትራ', 'cnShortEn' => 'Eritrea'],
            ['cnPhone' => '34', 'cnShortLocal' => 'España', 'cnShortEn' => 'Spain'],
            ['cnPhone' => '251', 'cnShortLocal' => 'ኢትዮጵያ', 'cnShortEn' => 'Ethiopia'],
            ['cnPhone' => '358', 'cnShortLocal' => 'Suomi', 'cnShortEn' => 'Finland'],
            ['cnPhone' => '679', 'cnShortLocal' => 'Fiji / Viti', 'cnShortEn' => 'Fiji'],
            ['cnPhone' => '500', 'cnShortLocal' => 'Falkland Islands', 'cnShortEn' => 'Falkland Islands'],
            ['cnPhone' => '691', 'cnShortLocal' => 'Micronesia', 'cnShortEn' => 'Micronesia'],
            ['cnPhone' => '298', 'cnShortLocal' => 'Føroyar / Færøerne', 'cnShortEn' => 'Faroes'],
            ['cnPhone' => '33', 'cnShortLocal' => 'France', 'cnShortEn' => 'France'],
            ['cnPhone' => '241', 'cnShortLocal' => 'Gabon', 'cnShortEn' => 'Gabon'],
            ['cnPhone' => '44', 'cnShortLocal' => 'United Kingdom', 'cnShortEn' => 'United Kingdom'],
            ['cnPhone' => '1473', 'cnShortLocal' => 'Grenada', 'cnShortEn' => 'Grenada'],
            ['cnPhone' => '995', 'cnShortLocal' => 'საქართველო', 'cnShortEn' => 'Georgia'],
            ['cnPhone' => '594', 'cnShortLocal' => 'Guyane française', 'cnShortEn' => 'French Guiana'],
            ['cnPhone' => '233', 'cnShortLocal' => 'Ghana', 'cnShortEn' => 'Ghana'],
            ['cnPhone' => '350', 'cnShortLocal' => 'Gibraltar', 'cnShortEn' => 'Gibraltar'],
            ['cnPhone' => '299', 'cnShortLocal' => 'Grønland', 'cnShortEn' => 'Greenland'],
            ['cnPhone' => '220', 'cnShortLocal' => 'Gambia', 'cnShortEn' => 'Gambia'],
            ['cnPhone' => '224', 'cnShortLocal' => 'Guinée', 'cnShortEn' => 'Guinea'],
            ['cnPhone' => '590', 'cnShortLocal' => 'Guadeloupe', 'cnShortEn' => 'Guadeloupe'],
            ['cnPhone' => '240', 'cnShortLocal' => 'Guinea Ecuatorial', 'cnShortEn' => 'Equatorial Guinea'],
            ['cnPhone' => '30', 'cnShortLocal' => 'Ελλάδα', 'cnShortEn' => 'Greece'],
            ['cnPhone' => '502', 'cnShortLocal' => 'Guatemala', 'cnShortEn' => 'Guatemala'],
            ['cnPhone' => '671', 'cnShortLocal' => 'Guåhån', 'cnShortEn' => 'Guam'],
            ['cnPhone' => '245', 'cnShortLocal' => 'Guiné-Bissau', 'cnShortEn' => 'Guinea-Bissau'],
            ['cnPhone' => '592', 'cnShortLocal' => 'Guyana', 'cnShortEn' => 'Guyana'],
            ['cnPhone' => '852', 'cnShortLocal' => '香港', 'cnShortEn' => 'Hong Kong SAR of China'],
            ['cnPhone' => '504', 'cnShortLocal' => 'Honduras', 'cnShortEn' => 'Honduras'],
            ['cnPhone' => '385', 'cnShortLocal' => 'Hrvatska', 'cnShortEn' => 'Croatia'],
            ['cnPhone' => '509', 'cnShortLocal' => 'Ayiti', 'cnShortEn' => 'Haiti'],
            ['cnPhone' => '36', 'cnShortLocal' => 'Magyarország', 'cnShortEn' => 'Hungary'],
            ['cnPhone' => '62', 'cnShortLocal' => 'Indonesia', 'cnShortEn' => 'Indonesia'],
            ['cnPhone' => '353', 'cnShortLocal' => 'Éire', 'cnShortEn' => 'Ireland'],
            ['cnPhone' => '972', 'cnShortLocal' => 'ישראל', 'cnShortEn' => 'Israel'],
            ['cnPhone' => '91', 'cnShortLocal' => 'India', 'cnShortEn' => 'India'],
            ['cnPhone' => '964', 'cnShortLocal' => 'العراق / عيَراق', 'cnShortEn' => 'Iraq'],
            ['cnPhone' => '98', 'cnShortLocal' => 'ايران', 'cnShortEn' => 'Iran'],
            ['cnPhone' => '354', 'cnShortLocal' => 'Ísland', 'cnShortEn' => 'Iceland'],
            ['cnPhone' => '39', 'cnShortLocal' => 'Italia', 'cnShortEn' => 'Italy'],
            ['cnPhone' => '1876', 'cnShortLocal' => 'Jamaica', 'cnShortEn' => 'Jamaica'],
            ['cnPhone' => '962', 'cnShortLocal' => 'أردنّ', 'cnShortEn' => 'Jordan'],
            ['cnPhone' => '81', 'cnShortLocal' => '日本', 'cnShortEn' => 'Japan'],
            ['cnPhone' => '254', 'cnShortLocal' => 'Kenya', 'cnShortEn' => 'Kenya'],
            ['cnPhone' => '996', 'cnShortLocal' => 'Кыргызстан', 'cnShortEn' => 'Kyrgyzstan'],
            ['cnPhone' => '855', 'cnShortLocal' => 'Kâmpŭchea', 'cnShortEn' => 'Cambodia'],
            ['cnPhone' => '686', 'cnShortLocal' => 'Kiribati', 'cnShortEn' => 'Kiribati'],
            ['cnPhone' => '269', 'cnShortLocal' => 'اتحاد القمر', 'cnShortEn' => 'Comoros'],
            [
                'cnPhone'      => '1869',
                'cnShortLocal' => 'Saint Kitts and Nevis',
                'cnShortEn'    => 'Saint Kitts and Nevis'
            ],
            ['cnPhone' => '850', 'cnShortLocal' => '북조선', 'cnShortEn' => 'North Korea'],
            ['cnPhone' => '82', 'cnShortLocal' => '한국', 'cnShortEn' => 'South Korea'],
            ['cnPhone' => '965', 'cnShortLocal' => 'الكويت', 'cnShortEn' => 'Kuwait'],
            ['cnPhone' => '1345', 'cnShortLocal' => 'Cayman Islands', 'cnShortEn' => 'Cayman Islands'],
            ['cnPhone' => '7', 'cnShortLocal' => 'Қазақстан /Казахстан', 'cnShortEn' => 'Kazakhstan'],
            ['cnPhone' => '856', 'cnShortLocal' => 'ເມືອງລາວ', 'cnShortEn' => 'Laos'],
            ['cnPhone' => '961', 'cnShortLocal' => 'لبنان', 'cnShortEn' => 'Lebanon'],
            ['cnPhone' => '1758', 'cnShortLocal' => 'Saint Lucia', 'cnShortEn' => 'Saint Lucia'],
            ['cnPhone' => '423', 'cnShortLocal' => 'Liechtenstein', 'cnShortEn' => 'Liechtenstein'],
            ['cnPhone' => '94', 'cnShortLocal' => 'ශ්‍රී ලංකා / இலங்கை', 'cnShortEn' => 'Sri Lanka'],
            ['cnPhone' => '231', 'cnShortLocal' => 'Liberia', 'cnShortEn' => 'Liberia'],
            ['cnPhone' => '266', 'cnShortLocal' => 'Lesotho', 'cnShortEn' => 'Lesotho'],
            ['cnPhone' => '370', 'cnShortLocal' => 'Lietuva', 'cnShortEn' => 'Lithuania'],
            ['cnPhone' => '352', 'cnShortLocal' => 'Luxemburg', 'cnShortEn' => 'Luxembourg'],
            ['cnPhone' => '371', 'cnShortLocal' => 'Latvija', 'cnShortEn' => 'Latvia'],
            ['cnPhone' => '218', 'cnShortLocal' => '‏ليبيا‎', 'cnShortEn' => 'Libya'],
            ['cnPhone' => '212', 'cnShortLocal' => 'المغربية', 'cnShortEn' => 'Morocco'],
            ['cnPhone' => '377', 'cnShortLocal' => 'Monaco', 'cnShortEn' => 'Monaco'],
            ['cnPhone' => '373', 'cnShortLocal' => 'Moldova', 'cnShortEn' => 'Moldova'],
            ['cnPhone' => '261', 'cnShortLocal' => 'Madagascar', 'cnShortEn' => 'Madagascar'],
            ['cnPhone' => '692', 'cnShortLocal' => 'Marshall Islands', 'cnShortEn' => 'Marshall Islands'],
            ['cnPhone' => '389', 'cnShortLocal' => 'Македонија', 'cnShortEn' => 'Macedonia'],
            ['cnPhone' => '223', 'cnShortLocal' => 'Mali', 'cnShortEn' => 'Mali'],
            ['cnPhone' => '95', 'cnShortLocal' => 'Myanmar', 'cnShortEn' => 'Myanmar'],
            ['cnPhone' => '976', 'cnShortLocal' => 'Монгол Улс', 'cnShortEn' => 'Mongolia'],
            ['cnPhone' => '853', 'cnShortLocal' => '澳門 / Macau', 'cnShortEn' => 'Macao SAR of China'],
            ['cnPhone' => '1670', 'cnShortLocal' => 'Northern Marianas', 'cnShortEn' => 'Northern Marianas'],
            ['cnPhone' => '596', 'cnShortLocal' => 'Martinique', 'cnShortEn' => 'Martinique'],
            ['cnPhone' => '222', 'cnShortLocal' => 'الموريتانية', 'cnShortEn' => 'Mauritania'],
            ['cnPhone' => '1664', 'cnShortLocal' => 'Montserrat', 'cnShortEn' => 'Montserrat'],
            ['cnPhone' => '356', 'cnShortLocal' => 'Malta', 'cnShortEn' => 'Malta'],
            ['cnPhone' => '230', 'cnShortLocal' => 'Mauritius', 'cnShortEn' => 'Mauritius'],
            ['cnPhone' => '960', 'cnShortLocal' => 'ޖުމުހޫރިއްޔ', 'cnShortEn' => 'Maldives'],
            ['cnPhone' => '265', 'cnShortLocal' => 'Malawi', 'cnShortEn' => 'Malawi'],
            ['cnPhone' => '52', 'cnShortLocal' => 'México', 'cnShortEn' => 'Mexico'],
            ['cnPhone' => '60', 'cnShortLocal' => 'مليسيا', 'cnShortEn' => 'Malaysia'],
            ['cnPhone' => '258', 'cnShortLocal' => 'Moçambique', 'cnShortEn' => 'Mozambique'],
            ['cnPhone' => '264', 'cnShortLocal' => 'Namibia', 'cnShortEn' => 'Namibia'],
            ['cnPhone' => '687', 'cnShortLocal' => 'Nouvelle-Calédonie', 'cnShortEn' => 'New Caledonia'],
            ['cnPhone' => '227', 'cnShortLocal' => 'Niger', 'cnShortEn' => 'Niger'],
            ['cnPhone' => '6723', 'cnShortLocal' => 'Norfolk Island', 'cnShortEn' => 'Norfolk Island'],
            ['cnPhone' => '234', 'cnShortLocal' => 'Nigeria', 'cnShortEn' => 'Nigeria'],
            ['cnPhone' => '505', 'cnShortLocal' => 'Nicaragua', 'cnShortEn' => 'Nicaragua'],
            ['cnPhone' => '31', 'cnShortLocal' => 'Nederland', 'cnShortEn' => 'Netherlands'],
            ['cnPhone' => '47', 'cnShortLocal' => 'Norge', 'cnShortEn' => 'Norway'],
            ['cnPhone' => '977', 'cnShortLocal' => 'नेपाल', 'cnShortEn' => 'Nepal'],
            ['cnPhone' => '674', 'cnShortLocal' => 'Naoero', 'cnShortEn' => 'Nauru'],
            ['cnPhone' => '683', 'cnShortLocal' => 'Niue', 'cnShortEn' => 'Niue'],
            ['cnPhone' => '64', 'cnShortLocal' => 'New Zealand / Aotearoa', 'cnShortEn' => 'New Zealand'],
            ['cnPhone' => '968', 'cnShortLocal' => 'عُمان', 'cnShortEn' => 'Oman'],
            ['cnPhone' => '507', 'cnShortLocal' => 'Panamá', 'cnShortEn' => 'Panama'],
            ['cnPhone' => '51', 'cnShortLocal' => 'Perú', 'cnShortEn' => 'Peru'],
            ['cnPhone' => '689', 'cnShortLocal' => 'Polynésie française', 'cnShortEn' => 'French Polynesia'],
            [
                'cnPhone'      => '675',
                'cnShortLocal' => 'Papua New Guinea  / Papua Niugini',
                'cnShortEn'    => 'Papua New Guinea'
            ],
            ['cnPhone' => '63', 'cnShortLocal' => 'Philippines', 'cnShortEn' => 'Philippines'],
            ['cnPhone' => '92', 'cnShortLocal' => 'پاکستان', 'cnShortEn' => 'Pakistan'],
            ['cnPhone' => '48', 'cnShortLocal' => 'Polska', 'cnShortEn' => 'Poland'],
            [
                'cnPhone'      => '508',
                'cnShortLocal' => 'Saint-Pierre-et-Miquelon',
                'cnShortEn'    => 'Saint Pierre and Miquelon'
            ],
            ['cnPhone' => '1787', 'cnShortLocal' => 'Puerto Rico', 'cnShortEn' => 'Puerto Rico'],
            ['cnPhone' => '351', 'cnShortLocal' => 'Portugal', 'cnShortEn' => 'Portugal'],
            ['cnPhone' => '680', 'cnShortLocal' => 'Belau / Palau', 'cnShortEn' => 'Palau'],
            ['cnPhone' => '595', 'cnShortLocal' => 'Paraguay', 'cnShortEn' => 'Paraguay'],
            ['cnPhone' => '974', 'cnShortLocal' => 'قطر', 'cnShortEn' => 'Qatar'],
            ['cnPhone' => '262', 'cnShortLocal' => 'Réunion', 'cnShortEn' => 'Reunion'],
            ['cnPhone' => '40', 'cnShortLocal' => 'România', 'cnShortEn' => 'Romania'],
            ['cnPhone' => '7', 'cnShortLocal' => 'Росси́я', 'cnShortEn' => 'Russia'],
            ['cnPhone' => '250', 'cnShortLocal' => 'Rwanda', 'cnShortEn' => 'Rwanda'],
            ['cnPhone' => '966', 'cnShortLocal' => 'السعودية', 'cnShortEn' => 'Saudi Arabia'],
            ['cnPhone' => '677', 'cnShortLocal' => 'Solomon Islands', 'cnShortEn' => 'Solomon Islands'],
            ['cnPhone' => '248', 'cnShortLocal' => 'Seychelles', 'cnShortEn' => 'Seychelles'],
            ['cnPhone' => '249', 'cnShortLocal' => 'السودان', 'cnShortEn' => 'Sudan'],
            ['cnPhone' => '46', 'cnShortLocal' => 'Sverige', 'cnShortEn' => 'Sweden'],
            ['cnPhone' => '65', 'cnShortLocal' => 'Singapore', 'cnShortEn' => 'Singapore'],
            [
                'cnPhone'      => '290',
                'cnShortLocal' => 'Saint Helena, Ascension and Tristan da Cunha',
                'cnShortEn'    => 'Saint Helena, Ascension and Tristan da Cunha'
            ],
            ['cnPhone' => '386', 'cnShortLocal' => 'Slovenija', 'cnShortEn' => 'Slovenia'],
            ['cnPhone' => '47', 'cnShortLocal' => 'Svalbard', 'cnShortEn' => 'Svalbard'],
            ['cnPhone' => '421', 'cnShortLocal' => 'Slovensko', 'cnShortEn' => 'Slovakia'],
            ['cnPhone' => '232', 'cnShortLocal' => 'Sierra Leone', 'cnShortEn' => 'Sierra Leone'],
            ['cnPhone' => '378', 'cnShortLocal' => 'San Marino', 'cnShortEn' => 'San Marino'],
            ['cnPhone' => '221', 'cnShortLocal' => 'Sénégal', 'cnShortEn' => 'Senegal'],
            ['cnPhone' => '252', 'cnShortLocal' => 'Soomaaliya', 'cnShortEn' => 'Somalia'],
            ['cnPhone' => '597', 'cnShortLocal' => 'Suriname', 'cnShortEn' => 'Suriname'],
            [
                'cnPhone'      => '239',
                'cnShortLocal' => 'São Tomé e Príncipe',
                'cnShortEn'    => 'São Tomé e Príncipe'
            ],
            ['cnPhone' => '503', 'cnShortLocal' => 'El Salvador', 'cnShortEn' => 'El Salvador'],
            ['cnPhone' => '963', 'cnShortLocal' => 'سوري', 'cnShortEn' => 'Syria'],
            ['cnPhone' => '268', 'cnShortLocal' => 'weSwatini', 'cnShortEn' => 'Swaziland'],
            [
                'cnPhone'      => '1649',
                'cnShortLocal' => 'Turks and Caicos Islands',
                'cnShortEn'    => 'Turks and Caicos Islands'
            ],
            ['cnPhone' => '235', 'cnShortLocal' => 'تشاد / Tchad', 'cnShortEn' => 'Chad'],
            ['cnPhone' => '228', 'cnShortLocal' => 'Togo', 'cnShortEn' => 'Togo'],
            ['cnPhone' => '66', 'cnShortLocal' => 'ไทย', 'cnShortEn' => 'Thailand'],
            ['cnPhone' => '992', 'cnShortLocal' => 'Тоҷикистон', 'cnShortEn' => 'Tajikistan'],
            ['cnPhone' => '993', 'cnShortLocal' => 'Türkmenistan', 'cnShortEn' => 'Turkmenistan'],
            ['cnPhone' => '216', 'cnShortLocal' => 'التونسية', 'cnShortEn' => 'Tunisia'],
            ['cnPhone' => '676', 'cnShortLocal' => 'Tonga', 'cnShortEn' => 'Tonga'],
            ['cnPhone' => '670', 'cnShortLocal' => 'Timor Lorosa\'e', 'cnShortEn' => 'Timor-Leste'],
            ['cnPhone' => '90', 'cnShortLocal' => 'Türkiye', 'cnShortEn' => 'Turkey'],
            [
                'cnPhone'      => '1868',
                'cnShortLocal' => 'Trinidad and Tobago',
                'cnShortEn'    => 'Trinidad and Tobago'
            ],
            ['cnPhone' => '688', 'cnShortLocal' => 'Tuvalu', 'cnShortEn' => 'Tuvalu'],
            ['cnPhone' => '886', 'cnShortLocal' => '中華', 'cnShortEn' => 'Taiwan'],
            ['cnPhone' => '255', 'cnShortLocal' => 'Tanzania', 'cnShortEn' => 'Tanzania'],
            ['cnPhone' => '380', 'cnShortLocal' => 'Україна', 'cnShortEn' => 'Ukraine'],
            ['cnPhone' => '256', 'cnShortLocal' => 'Uganda', 'cnShortEn' => 'Uganda'],
            ['cnPhone' => '1', 'cnShortLocal' => 'United States', 'cnShortEn' => 'United States'],
            ['cnPhone' => '598', 'cnShortLocal' => 'Uruguay', 'cnShortEn' => 'Uruguay'],
            ['cnPhone' => '998', 'cnShortLocal' => 'O‘zbekiston', 'cnShortEn' => 'Uzbekistan'],
            ['cnPhone' => '396', 'cnShortLocal' => 'Vaticano', 'cnShortEn' => 'Vatican City'],
            [
                'cnPhone'      => '1784',
                'cnShortLocal' => 'Saint Vincent and the Grenadines',
                'cnShortEn'    => 'Saint Vincent and the Grenadines'
            ],
            ['cnPhone' => '58', 'cnShortLocal' => 'Venezuela', 'cnShortEn' => 'Venezuela'],
            [
                'cnPhone'      => '1284',
                'cnShortLocal' => 'British Virgin Islands',
                'cnShortEn'    => 'British Virgin Islands'
            ],
            ['cnPhone' => '1340', 'cnShortLocal' => 'US Virgin Islands', 'cnShortEn' => 'US Virgin Islands'],
            ['cnPhone' => '84', 'cnShortLocal' => 'Việt Nam', 'cnShortEn' => 'Vietnam'],
            ['cnPhone' => '678', 'cnShortLocal' => 'Vanuatu', 'cnShortEn' => 'Vanuatu'],
            ['cnPhone' => '681', 'cnShortLocal' => 'Wallis and Futuna', 'cnShortEn' => 'Wallis and Futuna'],
            ['cnPhone' => '685', 'cnShortLocal' => 'Samoa', 'cnShortEn' => 'Samoa'],
            ['cnPhone' => '967', 'cnShortLocal' => 'اليمنية', 'cnShortEn' => 'Yemen'],
            ['cnPhone' => '269', 'cnShortLocal' => 'Mayotte', 'cnShortEn' => 'Mayotte'],
            ['cnPhone' => '27', 'cnShortLocal' => 'Afrika-Borwa', 'cnShortEn' => 'South Africa'],
            ['cnPhone' => '260', 'cnShortLocal' => 'Zambia', 'cnShortEn' => 'Zambia'],
            ['cnPhone' => '263', 'cnShortLocal' => 'Zimbabwe', 'cnShortEn' => 'Zimbabwe'],
            [
                'cnPhone'      => '381',
                'cnShortLocal' => 'Србија и Црна Гора',
                'cnShortEn'    => 'Serbia and Montenegro'
            ],
            ['cnPhone' => '35818', 'cnShortLocal' => 'Åland', 'cnShortEn' => 'Åland'],
            ['cnPhone' => '382', 'cnShortLocal' => 'Crna Gora', 'cnShortEn' => 'Montenegro'],
            ['cnPhone' => '381', 'cnShortLocal' => 'Srbija', 'cnShortEn' => 'Serbia'],
            ['cnPhone' => '44', 'cnShortLocal' => 'Jersey', 'cnShortEn' => 'Jersey'],
            ['cnPhone' => '44', 'cnShortLocal' => 'Guernsey', 'cnShortEn' => 'Guernsey'],
            ['cnPhone' => '44', 'cnShortLocal' => 'Mann / Mannin', 'cnShortEn' => 'Isle of Man'],
            ['cnPhone' => '590', 'cnShortLocal' => 'Saint-Martin', 'cnShortEn' => 'Saint Martin'],
            ['cnPhone' => '590', 'cnShortLocal' => 'Saint-Barthélemy', 'cnShortEn' => 'Saint Barthélemy'],
            [
                'cnPhone'      => '599',
                'cnShortLocal' => 'Bonaire, Sint Eustatius en Saba',
                'cnShortEn'    => 'Bonaire, Sint Eustatius and Saba'
            ],
            ['cnPhone' => '599', 'cnShortLocal' => 'Curaçao', 'cnShortEn' => 'Curaçao'],
            ['cnPhone' => '599', 'cnShortLocal' => 'Sint Maarten', 'cnShortEn' => 'Sint Maarten'],
            ['cnPhone' => '211', 'cnShortLocal' => 'South Sudan', 'cnShortEn' => 'South Sudan']
        ];

        /**
         * Find a list of countries by an international phone number
         *
         * @param int $intlPhoneNumer International phone number
         *
         * @return null Matching countries
         */
        public function findByIntlPhoneNumber($intlPhoneNumer)
        {
            $intlPhoneNumer = preg_replace('/[^0-9]+/', '', $intlPhoneNumer);
            if ($intlPhoneNumer[0] === '0') {
                return null;
            }

            $countries = [];
            foreach (self::$countries as $country) {
                if (strpos($country['cnPhone'], $intlPhoneNumer) === 0) {
                    $countries[] = $country;
                }
            }

            // Sort by match length
            usort($countries, function(array $country1, array $country2) {
                $country1Length = strlen($country1['cnPhone']);
                $country2Length = strlen($country2['cnPhone']);
                if ($country1Length == $country2Length) {
                    return 0;
                }

                return ($country1Length > $country2Length) ? -1 : 1;
            });

            return $countries;
        }
    }
}
