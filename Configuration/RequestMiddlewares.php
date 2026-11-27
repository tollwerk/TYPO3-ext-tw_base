<?php

/**
 * Middlewares of tw_base
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

use Tollwerk\TwBase\Component\Example\Middleware\ExampleRequest;
use Tollwerk\TwBase\Component\Example\Middleware\ExampleResponse;

// An example of a component renders in the frontend of its page, see Tollwerk\TwBase\Component\Example\ExampleEndpoint
return [
    'frontend' => [
        'tollwerk/tw-base/example-request' => [
            'target' => ExampleRequest::class,
            'after' => ['typo3/cms-frontend/page-argument-validator'],
            'before' => ['typo3/cms-frontend/prepare-tsfe-rendering'],
        ],
        'tollwerk/tw-base/example-response' => [
            'target' => ExampleResponse::class,
            'after' => ['typo3/cms-frontend/prepare-tsfe-rendering'],
            'before' => ['typo3/cms-frontend/shortcut-and-mountpoint-redirect'],
        ],
    ],
];
