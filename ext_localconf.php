<?php

use Tollwerk\TwBase\Command\ImageCommandController;
use Tollwerk\TwBase\LinkHandling\TelLinkBuilder;
use Tollwerk\TwBase\Service\MozjpegCompressorService;
use Tollwerk\TwBase\Service\PrimitiveLqipService;
use Tollwerk\TwBase\Service\Resource\Processing\ImageConvertTask;
use Tollwerk\TwBase\Service\Resource\Processing\ImageCropScaleMaskCompressTask;
use Tollwerk\TwBase\Service\SvgoCompressorService;
use Tollwerk\TwBase\Service\WebpConverterService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\DatabaseWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Tollwerk']['TwBase']['writerConfiguration'] = [
    LogLevel::WARNING => [DatabaseWriter::class => []],
];

// Register the Primitive LQIP service
ExtensionManagementUtility::addService(
    $_EXTKEY,
    'lqip', // Service type
    'tx_twbase_primitive', // Service key
    array(
        'title'       => 'Primitive',
        'description' => 'Create low-quality image previews (LQIP) with geometric shapes',
        'subtype'     => 'jpg,png,gif',
        'available'   => true,
        'priority'    => 60,
        'quality'     => 80,
        'os'          => '',
        'exec'        => 'primitive,svgo',
        'className'   => PrimitiveLqipService::class
    )
);

// Register the mozjpeg image compressor service
ExtensionManagementUtility::addService(
    $_EXTKEY,
    'filecompress', // Service type
    'tx_twbase_mozjpeg', // Service key
    array(
        'title'       => 'mozjpeg',
        'description' => 'Compress JPEG images using the mozjpeg encoder (https://github.com/mozilla/mozjpeg)',
        'subtype'     => 'jpg',
        'available'   => true,
        'priority'    => 60,
        'quality'     => 80,
        'os'          => '',
        'exec'        => 'mozjpeg',
        'className'   => MozjpegCompressorService::class
    )
);

// Register the SVGO image compressor service
ExtensionManagementUtility::addService(
    $_EXTKEY,
    'filecompress', // Service type
    'tx_twbase_svgo', // Service key
    array(
        'title'       => 'svgo',
        'description' => 'Compress SVG vector graphics using the SVGO optimizer (https://github.com/svg/svgo)',
        'subtype'     => 'svg',
        'available'   => true,
        'priority'    => 60,
        'quality'     => 80,
        'os'          => '',
        'exec'        => 'svgo',
        'className'   => SvgoCompressorService::class
    )
);

// Register the WebP image converter service
ExtensionManagementUtility::addService(
    $_EXTKEY,
    'fileconvert', // Service type
    'tx_twbase_webp', // Service key
    array(
        'title'       => 'webp',
        'description' => 'Convert images using the Google WebP converter (https://developers.google.com/speed/webp)',
        'subtype'     => 'webp',
        'available'   => true,
        'priority'    => 60,
        'quality'     => 80,
        'os'          => '',
        'exec'        => 'cwebp',
        'className'   => WebpConverterService::class
    )
);

// Register additional image processing tasks
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskCompress'] = ImageCropScaleMaskCompressTask::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.Convert']               = ImageConvertTask::class;

// Extend the local image processor
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor'] = [
    'className' => 'Tollwerk\\TwBase\\Service\\Resource\\Processing\\LocalImageProcessor',
];

// Register the tel link builder
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['tel'] = TelLinkBuilder::class;

// Register an icon for the tel links
GeneralUtility::makeInstance(IconRegistry::class)->registerIcon(
    'tx-base-tel',
    BitmapIconProvider::class,
    ['source' => 'EXT:tw_base/Resources/Public/Icons/tel.png']
);

// Register the global Fluid viewhelper namespace (if specified)
$globalNSPrefix = trim(
    isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']) ?
        GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('tw_base', 'globalNSPrefix') :
        unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['tw_base'])['globalNSPrefix']
);
if (strlen($globalNSPrefix)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$globalNSPrefix] = ['Tollwerk\\TwBase\\ViewHelpers'];
}

// Register the component service command controller
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = ImageCommandController::class;

// Register the base viewhelper namespace
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['base'] = ['Tollwerk\\TwBase\\ViewHelpers'];