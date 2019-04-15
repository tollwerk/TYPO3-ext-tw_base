<?php

/**
 * tollwerk
 *
 * @category   Jkphl
 * @package    Jkphl\Rdfalite
 * @subpackage Tollwerk\TwBase\Utility
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

namespace Tollwerk\TwBase\Utility;

use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * TCA utility
 */
class TcaUtility
{
    /**
     * Supported languages
     *
     * SELECT CONCAT(`lg_name_local`, " (", lg_name_en, ")") AS label, IF(lg_collate_locale = "", LOWER(lg_iso_2),
     * lg_collate_locale) AS iso FROM `static_languages` ORDER BY lg_name_en ASC
     *
     * @var array
     */
    public static $languages = [
        ['LLL:EXT:tw_base/Resources/Private/Language/locallang_db.xlf:pages.tx_twbase_title_language.auto', ''],
        ['Аҧсуа бызшәа (Abkhazian)', 'ab'],
        ['Afaraf (Afar)', 'aa'],
        ['Afrikaans (Afrikaans)', 'af'],
        ['Akan (Akan)', 'ak'],
        ['Gjuha shqipe (Albanian)', 'sq'],
        ['አማርኛ (Amharic)', 'am'],
        ['العربية (Arabic)', 'ar_SA'],
        ['Aragonés (Aragonese)', 'an'],
        ['Հայերեն (Armenian)', 'hy'],
        ['অসমীয়া (Assamese)', 'as'],
        ['магӀарул мацӀ (Avar)', 'av'],
        ['Avestan (Avestan)', 'ae'],
        ['Aymar aru (Aymara)', 'ay'],
        ['Azərbaycan dili (Azerbaijani)', 'az'],
        ['Bamanankan (Bambara)', 'bm'],
        ['Башҡорт (Bashkir)', 'ba'],
        ['Euskara (Basque)', 'eu_ES'],
        ['Беларуская (Belarusian)', 'be'],
        ['বাংলা (Bengali)', 'bn'],
        ['भोजपुरी (Bihari)', 'bh'],
        ['Bislama (Bislama)', 'bi'],
        ['Bosanski (Bosnian)', 'bs_BA'],
        ['Português brasileiro (Brazilian Portuguese)', 'pt_BR'],
        ['Brezhoneg (Breton)', 'br'],
        ['Български (Bulgarian)', 'bg_BG'],
        ['မ္ရန္‌မာစာ (Burmese)', 'my_MM'],
        ['Français canadien (Canadian French)', 'fr_CA'],
        ['Català (Catalan)', 'ca_ES'],
        ['Chamoru (Chamorro)', 'ch'],
        ['Нохчийн (Chechen)', 'ce'],
        ['chiCheŵa (Chichewa)', 'ny'],
        ['汉语 (Chinese (Simplified))', 'zh_CN'],
        ['漢語 (Chinese (Traditional))', 'zh_HK'],
        ['церковнославя́нский язы́к (Church Slavonic)', 'cu'],
        ['Чăваш чěлхи (Chuvash)', 'cv'],
        ['Kernewek (Cornish)', 'kw'],
        ['Corsu (Corsican)', 'co'],
        ['ᓀᐦᐃᔭᐤ (Cree)', 'cr'],
        ['Hrvatski (Croatian)', 'hr_HR'],
        ['Čeština (Czech)', 'cs_CZ'],
        ['Dansk (Danish)', 'da_DK'],
        ['ދިވެހި (Dhivehi)', 'dv'],
        ['Nederlands (Dutch)', 'nl_NL'],
        ['ཇོང་ཁ (Dzongkha)', 'dz'],
        ['English (English)', 'en'],
        ['English [United Kingdom] (English (United Kingdom))', 'en_GB'],
        ['English [USA] (English (USA))', 'en_US'],
        ['Esperanto (Esperanto)', 'eo'],
        ['Eesti (Estonian)', 'et_EE'],
        ['Ɛʋɛgbɛ (Ewe)', 'ee'],
        ['Føroyskt (Faeroese)', 'fo_FO'],
        ['Na Vosa Vakaviti (Fijian)', 'fj'],
        ['Filipino (Filipino)', 'fil'],
        ['Suomi (Finnish)', 'fi_FI'],
        ['Français (French)', 'fr_FR'],
        ['Frysk (Frisian)', 'fy'],
        ['Fulfulde / Pulaar (Fula)', 'ff'],
        ['Galego (Galician)', 'gl_ES'],
        ['ქართული (Georgian)', 'ka'],
        ['Deutsch (German)', 'de_DE'],
        ['Deutsch [Österreich] (German (Austria))', 'de_AT'],
        ['Deutsch [Schweiz] (German (Switzerland))', 'de_CH'],
        ['Ελληνικά (Greek)', 'el_GR'],
        ['Kalaallisut (Greenlandic)', 'kl_DK'],
        ['Avañe\'ẽ (Guaraní)', 'gn'],
        ['ગુજરાતી (Gujarati)', 'gu'],
        ['Krèyol ayisyen (Haïtian Creole)', 'ht'],
        ['Hausa (Hausa)', 'ha'],
        ['עברית (Hebrew)', 'he_IL'],
        ['otsiHerero (Herero)', 'hz'],
        ['हिन्दी (Hindi)', 'hi_IN'],
        ['Hiri motu (Hiri motu)', 'ho'],
        ['Magyar (Hungarian)', 'hu_HU'],
        ['Íslenska (Icelandic)', 'is_IS'],
        ['Ido (Ido)', 'io'],
        ['Igbo (Igbo)', 'ig'],
        ['Bahasa Indonesia (Indonesian)', 'id'],
        ['Interlingua (Interlingua)', 'ia'],
        ['Interlingue (Interlingue)', 'ie'],
        ['ᐃᓄᒃᑎᑐᑦ (Inuktitut)', 'iu'],
        ['Iñupiak (Inupiaq)', 'ik'],
        ['Gaeilge (Irish)', 'ga'],
        ['Italiano (Italian)', 'it_IT'],
        ['日本語 (Japanese)', 'ja_JP'],
        ['Basa Jawa (Javanese)', 'jv'],
        ['ಕನ್ನಡ (Kannada)', 'kn'],
        ['Kanuri (Kanuri)', 'kr'],
        ['कॉशुर (Kashmiri)', 'ks'],
        ['Қазақ тілі (Kazakh)', 'kk'],
        ['ភាសាខ្មែរ (Khmer)', 'km'],
        ['Gĩkũyũ (Kikuyu)', 'ki'],
        ['Kinyarwanda (Kinyarwanda)', 'rw'],
        ['Кыргыз тили (Kirghiz)', 'ky'],
        ['kiRundi (Kirundi)', 'rn'],
        ['коми кыв (Komi)', 'kv'],
        ['Kikongo (Kongo)', 'kg'],
        ['한국말 (Korean)', 'ko_KR'],
        ['Kuanyama (Kuanyama)', 'kj'],
        ['Kurdî (Kurdish)', 'ku'],
        ['ພາສາລາວ (Lao)', 'lo'],
        ['Lingua latina (Latin)', 'la'],
        ['Latviešu (Latvian)', 'lv_LV'],
        ['Limburgs (Limburgish)', 'li'],
        ['Lingála (Lingala)', 'ln'],
        ['Lietuvių (Lithuanian)', 'lt_LT'],
        ['Luba-Katanga (Luba-Katanga)', 'lu'],
        ['Luganda (Luganda)', 'lg'],
        ['Lëtzebuergesch (Luxembourgish)', 'lb'],
        ['Македонски (Macedonian)', 'mk'],
        ['Merina (Malagasy)', 'mg'],
        ['Bahasa Melayu (Malay)', 'ms'],
        ['മലയാളം (Malayalam)', 'ml'],
        ['Malti (Maltese)', 'mt_MT'],
        ['Gaelg (Manx)', 'gv'],
        ['Māori (Māori)', 'mi'],
        ['मराठी (Marathi)', 'mr'],
        ['Kajin M̧ajeļ (Marshallese)', 'mh'],
        ['молдовеняскэ (Moldavian)', 'mo'],
        ['Монгол (Mongolian)', 'mn'],
        ['Crnogorski jezik (Montenegrin)', 'sr_ME'],
        ['Ekakairũ Naoero (Nauru)', 'na'],
        ['Dinékʼehǰí (Navajo)', 'nv'],
        ['Owambo (Ndonga)', 'ng'],
        ['नेपाली (Nepali)', 'ne'],
        ['isiNdebele (North Ndebele)', 'nd'],
        [' Sámegiella (Northern Sami)', 'se'],
        ['Norsk (Norwegian)', 'no_NO'],
        ['Norsk bokmål (Norwegian Bokmål)', 'nb'],
        ['Norsk nynorsk (Norwegian Nynorsk)', 'nn'],
        ['Occitan (Occitan)', 'oc'],
        ['ᐊᓂᔑᓈᐯᒧᐎᓐ (Ojibwa)', 'oj'],
        ['ଓଡ଼ିଆ (Oriya)', 'or'],
        ['Afaan Oromoo (Oromo)', 'om'],
        ['Ирон æвзаг (Ossetic)', 'os'],
        ['Pāli (Pali)', 'pi'],
        ['پښت (Pashto)', 'ps'],
        ['فارسی (Persian)', 'fa_IR'],
        ['Polski (Polish)', 'pl_PL'],
        ['Português (Portuguese)', 'pt_PT'],
        ['ਪੰਜਾਬੀ / پنجابی (Punjabi)', 'pa'],
        ['Runa Simi (Quechua)', 'qu'],
        ['Rumantsch (Rhaeto-Romance)', 'rm'],
        ['Română (Romanian)', 'ro_RO'],
        ['Русский (Russian)', 'ru_RU'],
        ['Gagana faʼa Samoa (Samoan)', 'sm'],
        ['Sängö (Sango)', 'sg'],
        ['संस्कृतम् (Sanskrit)', 'sa'],
        ['Sardu (Sardinian)', 'sc'],
        ['Gàidhlig (Scottish Gaelic)', 'gd'],
        ['Српски / Srpski (Serbian)', 'sr_YU'],
        ['seSotho (Sesotho)', 'st'],
        ['Setswana (Setswana)', 'tn'],
        ['chiShona (Shona)', 'sn'],
        ['سنڌي، سندھی (Sindhi)', 'sd'],
        ['සිංහල (Sinhala)', 'si'],
        ['Slovenčina (Slovak)', 'sk_SK'],
        ['Slovenščina (Slovenian)', 'sl_SI'],
        ['af Soomaali (Somali)', 'so'],
        ['Ndébélé (South Ndebele)', 'nr'],
        ['Español (Spanish)', 'es_ES'],
        ['Basa Sunda (Sundanese)', 'su'],
        ['Kiswahili (Swahili)', 'sw'],
        ['siSwati (Swati)', 'ss'],
        ['Svenska (Swedish)', 'sv_SE'],
        ['Tagalog (Tagalog)', 'tl'],
        ['Reo Tahiti (Tahitian)', 'ty'],
        ['тоҷикӣ / تاجیکی (Tajik)', 'tg'],
        ['தமிழ் (Tamil)', 'ta'],
        ['татарча / tatarça / تاتارچ (Tatar)', 'tt'],
        ['తెలుగు (Telugu)', 'te'],
        ['ภาษาไทย (Thai)', 'th_TH'],
        ['བོད་ཡིག (Tibetan)', 'bo'],
        ['ትግርኛ (Tigrinya)', 'ti'],
        ['faka-Tonga (Tongan)', 'to'],
        ['Tsonga (Tsonga)', 'ts'],
        ['Türkçe (Turkish)', 'tr_TR'],
        ['Türkmen dili (Turkmen)', 'tk'],
        ['Twi (Twi)', 'tw'],
        ['Українська (Ukrainian)', 'uk_UA'],
        ['اردو (Urdu)', 'ur'],
        ['ئۇيغۇرچه (Uyghur)', 'ug'],
        ['Ўзбек / O\'zbek (Uzbek)', 'uz'],
        ['tshiVenḓa (Venda)', 've'],
        ['Tiếng Việt (Vietnamese)', 'vi_VN'],
        ['Volapük (Volapük)', 'vo'],
        ['Walon (Walloon)', 'wa'],
        ['Cymraeg (Welsh)', 'cy'],
        ['Wolof (Wolof)', 'wo'],
        ['isiXhosa (Xhosa)', 'xh'],
        ['ꆇꉙ (Yi)', 'ii'],
        ['ייִדיש (Yiddish)', 'yi'],
        ['Yorùbá (Yoruba)', 'yo'],
        ['Sawcuengh (Zhuang)', 'za'],
        ['isiZulu (Zulu)', 'zu']
    ];

    /**
     * Pre-processor for breakpoint specification presets
     *
     * @param array $config Select configuration
     *
     * @return array Modified select Configuration
     */
    public function responsiveImagesBreakpointsSpecifications(array $config)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var BackendConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get(BackendConfigurationManager::class);
        $setup                = $configurationManager->getTypoScriptSetup();
        list(, $conf) = GeneralUtility::makeInstance(TypoScriptParser::class)
                                      ->getVal('lib.contentElement.settings.media.breakpoints.presets', $setup);

        // Run through all defined presets
        foreach ($conf as $key => $configs) {
            $config['items'][] = [
                sprintf('%s (%s)', ucfirst($key), implode(', ', GeneralUtility::trimExplode(',', $configs, true))),
                $key,
            ];
        }

        return $config;
    }

    /**
     * Creates the string for TCA['types'][...]['showitem']
     *
     * @param array $showitem Array of divs, fields and palletes to show<p>
     *                        The first level is the name of the div, so use "General", "Access" etc. here.
     *                        The second level can be a string for a single field like "title" or "crdate" or an array
     *                        for rendering a palette. If you want to render a palette on the second level use an array
     *                        with pallete name as first key. Optional you can have a second key for palette label.</p>
     *                        <p>
     *                        Example:<br />
     *                        <code>
     *                        \Tollwerk\TwRws\Utility\TcaUtility::createShowitemString([
     *                          'General' => [
     *                              'title'
     *                              'description',
     *                              [PALETTE_NAME, OPTIONAL_PALETTE_LABEL]
     *                          ],
     *                          'Access' => [
     *                              'crdate',
     *                              [PALETTE_NAME, OPTIONAL_PALETTE_LABEL],
     *                          ]
     *                      ]);
     *                      </code>
     *                      </p>
     *
     * @return string
     */
    public static function createShowitemString(array $showitem = []): string
    {
        $showitemArray = [];
        $divCounter = 0;

        foreach ($showitem as $divName => $divFields) {
            $showitemArray[] = '--div--;' . ($divCounter > 0 ? $divName : 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general');
            foreach ($divFields as $field) {
                // Array means it's a palette
                if (is_array($field)) {
                    $showitemArray[] = '--palette--;'.(count($field) > 1 ? $field[1] : '').';'.$field[0];
                } else {
                    $showitemArray[] = $field;
                }
            }
            $divCounter++;
        }
        $string = implode(','.PHP_EOL, $showitemArray);
        return $string;
    }
}
