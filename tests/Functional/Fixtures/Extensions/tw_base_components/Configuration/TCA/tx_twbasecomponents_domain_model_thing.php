<?php

/**
 * Things, a table that no Classes.php names
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

return [
    'ctrl' => ['title' => 'Thing', 'label' => 'title'],
    'columns' => ['title' => ['label' => 'Title', 'config' => ['type' => 'input']]],
    'types' => ['0' => ['showitem' => 'title']],
];
