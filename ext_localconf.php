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

// The layout "TwBase" for mails, see Tollwerk\TwBase\Mail\Mailer
$layouts = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'] ?? null;
if (is_array($layouts)) {
    $layouts['tw_base'] = 'EXT:tw_base/Resources/Private/Layouts/Email/';
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'] = $layouts;
}

// The address of an example of a component needs no cache hash, see Tollwerk\TwBase\Component\Example\ExampleEndpoint
$excluded = $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] ?? [];
if (is_array($excluded)) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = [
        ...$excluded,
        Tollwerk\TwBase\Component\Example\ExampleEndpoint::PARAMETER,
        Tollwerk\TwBase\Component\Example\ExampleEndpoint::SIGNATURE,
    ];
}
