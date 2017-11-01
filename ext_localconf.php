<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Tollwerk']['TwBase']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
        \TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => []
    ],
];

// Register an LQIP service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    // Service type
    'lqip',
    // Service key
    'tx_twbase_primitive',
    array(
        'title' => 'Primitive',
        'description' => 'Create low-quality image previews (LQIP) with geometric shapes',

        'subtype' => 'jpg,png,gif',

        'available' => true,
        'priority' => 60,
        'quality' => 80,

        'os' => '',
        'exec' => 'primitive,svgo',

        'className' => \Tollwerk\TwBase\Service\PrimitiveLqipService::class
    )
);
