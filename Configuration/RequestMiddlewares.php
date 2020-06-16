<?php

use HTML\Sourceopt\Middleware\CleanHtmlMiddleware;

return [
    'frontend' => [
        'tollwerk/base/svg-sprite' => [
            'target' => \Tollwerk\TwBase\Middleware\SvgSpriteMiddleware::class,
            'after'  => [
                'typo3/cms-frontend/maintenance-mode',
            ],
            'before' => [
                'html/sourceopt/clean-html',
            ],
        ]
    ]
];
