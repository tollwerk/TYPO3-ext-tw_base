<?php

/**
 * Configuration of tw_base at the start of TYPO3
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

defined('TYPO3') || die();

$configuration = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
if (is_array($configuration)) {
    $mail = is_array($configuration['MAIL'] ?? null) ? $configuration['MAIL'] : [];
    $frontend = is_array($configuration['FE'] ?? null) ? $configuration['FE'] : [];
    $cacheHash = is_array($frontend['cacheHash'] ?? null) ? $frontend['cacheHash'] : [];
    $excluded = is_array($cacheHash['excludedParameters'] ?? null) ? $cacheHash['excludedParameters'] : [];

    $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($configuration, [
        // The layout "TwBase" for mails, see Tollwerk\TwBase\Mail\Mailer. The key is a number, as TYPO3 sorts the
        // paths by their numbers and appends those of a site with the next one; the core has 0 and 10
        'MAIL' => is_array($mail['layoutRootPaths'] ?? null)
            ? ['layoutRootPaths' => [20 => 'EXT:tw_base/Resources/Private/Layouts/Email/']]
            : [],
        // The address of an example of a component needs no cache hash, see
        // Tollwerk\TwBase\Component\Example\ExampleEndpoint
        'FE' => ['cacheHash' => ['excludedParameters' => [
            ...$excluded,
            Tollwerk\TwBase\Component\Example\ExampleEndpoint::PARAMETER,
            Tollwerk\TwBase\Component\Example\ExampleEndpoint::SIGNATURE,
        ]]],
    ]);
}
