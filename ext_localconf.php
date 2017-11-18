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
        'description' => 'Compress JPEG images using the mozjpeg encoder (https://github.com/mozilla/mozjpeg)',

        'subtype' => 'jpg',

        'available' => true,
        'priority' => 60,
        'quality' => 80,

        'os' => '',
        'exec' => 'mozjpeg',

        'className' => \Tollwerk\TwBase\Service\MozjpegCompressorService::class
    )
);

// Register the SVGO image compressor service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    // Service type
    'filecompress',
    // Service key
    'tx_twbase_svgo',
    array(
        'title' => 'svgo',
        'description' => 'Compress SVG vector graphics using the SVGO optimizer (https://github.com/svg/svgo)',

        'subtype' => 'svg',

        'available' => true,
        'priority' => 60,
        'quality' => 80,

        'os' => '',
        'exec' => 'svgo',

        'className' => \Tollwerk\TwBase\Service\SvgoCompressorService::class
    )
);

// Register the extended image compressing task
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskCompress'] = \Tollwerk\TwBase\Service\Resource\Processing\ImageCropScaleMaskCompressTask::class;

// Extend the local image processor
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor'] = [
    'className' => 'Tollwerk\\TwBase\\Service\\Resource\\Processing\\LocalImageProcessor',
];
