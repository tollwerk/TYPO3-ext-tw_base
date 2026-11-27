<?php

/**
 * Components of tw_base in the namespace all packages of tollwerk share
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

// Priority 10; other packages choose their own, see the README
return [
    'Tollwerk\\Components' => [
        'templatePaths' => [10 => 'EXT:tw_base/Resources/Private/Components'],
    ],
];
