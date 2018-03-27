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

// Register the WebP image converter service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    // Service type
    'fileconvert',
    // Service key
    'tx_twbase_webp',
    array(
        'title' => 'webp',
        'description' => 'Convert images using the Google WebP converter (https://developers.google.com/speed/webp)',

        'subtype' => 'webp',

        'available' => true,
        'priority' => 60,
        'quality' => 80,

        'os' => '',
        'exec' => 'cwebp',

        'className' => \Tollwerk\TwBase\Service\WebpConverterService::class
    )
);

// Register additional image processing tasks
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskCompress'] = \Tollwerk\TwBase\Service\Resource\Processing\ImageCropScaleMaskCompressTask::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.Convert'] = \Tollwerk\TwBase\Service\Resource\Processing\ImageConvertTask::class;

// Extend the local image processor
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor'] = [
    'className' => 'Tollwerk\\TwBase\\Service\\Resource\\Processing\\LocalImageProcessor',
];

// Register the tel link builder
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['tel'] = \Tollwerk\TwBase\LinkHandling\TelLinkBuilder::class;

// Register an icon for the tel links
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
    'tx-base-tel',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:tw_base/Resources/Public/Icons/tel.png']
);

// Register the global Fluid viewhelper namespace (if specified)
$globalNSPrefix = trim(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
    ->get('tw_base', 'globalNSPrefix'));
if (strlen($globalNSPrefix)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$globalNSPrefix] = ['Tollwerk\\TwBase\\ViewHelpers'];
}