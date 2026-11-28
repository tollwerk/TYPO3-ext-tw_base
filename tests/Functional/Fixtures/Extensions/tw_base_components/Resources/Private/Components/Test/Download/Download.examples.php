<?php

/**
 * Examples of the download of the tests
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

use TYPO3\CMS\Core\Resource\File;

return [
    'examples' => [
        'german' => [
            'label' => 'On a German page',
            'page' => 2,
            'language' => 1,
            'arguments' => ['file' => ['record' => 'sys_file', 'uid' => 1, 'model' => File::class]],
        ],
    ],
];
