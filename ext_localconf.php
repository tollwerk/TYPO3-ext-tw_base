<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Tollwerk']['TwBase']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
        \TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => []
    ],
];

// Register the Primitive LQIP service
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

// Register the mozjpeg image compressor service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    // Service type
    'filecompress',
    // Service key
    'tx_twbase_mozjpeg',
    array(
        'title' => 'mozjpeg',
        'description' => 'Compress images using the mozjpeg encoder (https://github.com/mozilla/mozjpeg)',

        'subtype' => 'jpg',

        'available' => true,
        'priority' => 60,
        'quality' => 80,

        'os' => '',
        'exec' => 'mozjpeg',

        'className' => \Tollwerk\TwBase\Service\MozjpegCompressorService::class
    )
);

// Connect to the file processing signals
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Core\Resource\ResourceStorage::class,
    'preFileProcess',
    \Tollwerk\TwBase\Utility\FileCompressorUtility::class,
    'registerCompressFile'
);
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
    \TYPO3\CMS\Core\Resource\ResourceStorage::class,
    'postFileProcess',
    \Tollwerk\TwBase\Utility\FileCompressorUtility::class,
    'compressFile'
);
