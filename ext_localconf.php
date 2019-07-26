<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Tollwerk']['TwBase']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::WARNING => [\TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => []],
];

// Register the Primitive LQIP service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
        'className'   => \Tollwerk\TwBase\Service\PrimitiveLqipService::class
    )
);

// Register the mozjpeg image compressor service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
        'className'   => \Tollwerk\TwBase\Service\MozjpegCompressorService::class
    )
);

// Register the SVGO image compressor service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
        'className'   => \Tollwerk\TwBase\Service\SvgoCompressorService::class
    )
);

// Register the WebP image converter service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
        'className'   => \Tollwerk\TwBase\Service\WebpConverterService::class
    )
);

// Register additional image processing tasks
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskCompress'] = \Tollwerk\TwBase\Service\Resource\Processing\ImageCropScaleMaskCompressTask::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.Convert']               = \Tollwerk\TwBase\Service\Resource\Processing\ImageConvertTask::class;

// Extend the local image processor
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalImageProcessor'] = [
    'className' => 'Tollwerk\\TwBase\\Service\\Resource\\Processing\\LocalImageProcessor',
];

// Register the tel link builder
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler']['tel'] = \Tollwerk\TwBase\LinkHandling\TelLinkBuilder::class;

// Override language files
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][] = 'EXT:tw_base/Resources/Private/Language/form_editor.xlf';

// Register an icon for the tel links
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
    'tx-base-tel',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:tw_base/Resources/Public/Icons/tel.png']
);
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
    'tx-base-formfield-clock',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:tw_base/Resources/Public/Icons/Clock.svg']
);

// Register the global Fluid viewhelper namespace (if specified)
$globalNSPrefix = trim(
    isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']) ?
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
                                              ->get('tw_base', 'globalNSPrefix') :
        unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['tw_base'])['globalNSPrefix']
);
if (strlen($globalNSPrefix)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$globalNSPrefix] = ['Tollwerk\\TwBase\\ViewHelpers'];
}

// Register the component service command controller
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \Tollwerk\TwBase\Command\ImageCommandController::class;

// Register the base viewhelper namespace
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['base'] = ['Tollwerk\\TwBase\\ViewHelpers'];

// Register a hook for injecting an SVG sprite
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = \Tollwerk\TwBase\Utility\SvgIconManager::class.'->injectSvgSprite';
