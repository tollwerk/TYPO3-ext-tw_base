<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 * @author     Klaus Fiedler <klaus@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Klaus Fiedler <klaus@tollwerk.de>
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

/**
 * Extended Localization Utility
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class LocalizationUtility extends \TYPO3\CMS\Extbase\Utility\LocalizationUtility
{
    /**
     * Returns the localized label of the LOCAL_LANG key, $key.
     *
     * @param string $key                       The key from the LOCAL_LANG array for which to return the value.
     * @param string|null $extensionName        The name of the extension
     * @param array $arguments                  The arguments of the extension, being passed over to vsprintf
     * @param string $languageKey               The language key or null for using the current language from the system
     * @param string[] $alternativeLanguageKeys The alternative language keys if no translation was found. If null and
     *                                          we are in the frontend, then the language_alt from TypoScript setup
     *                                          will be used
     *
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     */
    public static function translate(
        string $key,
        ?string $extensionName = null,
        array $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string {
        $localization = parent::translate($key, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
        if (strlen($localization)) {
            return $localization;
        }
        $key = explode(':', $key, 2);

        return array_pop($key);
    }

    /**
     * Reset the extension language cache for a particular extension
     *
     * @param string $extensionName Extension name
     */
    public static function resetExtensionLanguageCache($extensionName)
    {
        unset(static::$LOCAL_LANG[$extensionName]);
    }
}
