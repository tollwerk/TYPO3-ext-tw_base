<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase
 * @author     Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @copyright  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2019 Joschi Kuphal <joschi@tollwerk.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *f
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

call_user_func(
    function() {
        // Logging System configuration
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Tollwerk']['TwBase']['writerConfiguration'] = [
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
                \TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => []
            ],
        ];

        // Register video content element icon
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
            'base-video',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:tw_base/Resources/Public/Icons/Track.svg']
        );

        // Register the Primitive LQIP service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'tw_base',
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

        // Register the gzip compressor service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'tw_base',
            'filecompress', // Service type
            'tx_twbase_gzip', // Service key
            array(
                'title'       => 'gzip',
                'description' => 'Compress text resources using the gzip compressor',
                'subtype'     => 'css,js,txt,svg,json,html,xml',
                'available'   => true,
                'priority'    => 60,
                'quality'     => 80,
                'os'          => '',
                'exec'        => 'gzip',
                'className'   => \Tollwerk\TwBase\Service\GzipCompressorService::class
            )
        );

        // Register the brotli compressor service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'tw_base',
            'filecompress', // Service type
            'tx_twbase_brotli', // Service key
            array(
                'title'       => 'brotli',
                'description' => 'Compress text resources using the gzip compressor',
                'subtype'     => 'css,js,txt,svg,json,html,xml',
                'available'   => true,
                'priority'    => 70,
                'quality'     => 80,
                'os'          => '',
                'exec'        => 'brotli',
                'className'   => \Tollwerk\TwBase\Service\BrotliCompressorService::class
            )
        );

        // Register the mozjpeg image compressor service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'tw_base',
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
            'tw_base',
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
            'tw_base',
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

        // Add plugin for generic ajax calls. Add array to SC_OPTIONS for registering callable ajax functions
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/tw_base']['ajax'] = [];
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'TwBase',
            'Ajax',
            [\Tollwerk\TwBase\Controller\AjaxController::class => 'dispatch'],
            [\Tollwerk\TwBase\Controller\AjaxController::class => 'dispatch']
        );


        // Register additional image processing tasks
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.CropScaleMaskCompress'] = \Tollwerk\TwBase\Service\Resource\Processing\ImageCropScaleMaskCompressTask::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes']['Image.Convert']               = \Tollwerk\TwBase\Service\Resource\Processing\ImageConvertTask::class;

        // Extend the local image processor
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor::class] = [
            'className' => \Tollwerk\TwBase\Service\Resource\Processing\LocalImageProcessor::class,
        ];

        // Override language files
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][] = 'EXT:tw_base/Resources/Private/Language/form_editor.xlf';

        // Register icons
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
            'tx-base-formfield-clock',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:tw_base/Resources/Public/Icons/Clock.svg']
        );
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->registerIcon(
            'tx-base-formfield-call-to-action',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:tw_base/Resources/Public/Icons/Link.svg']
        );

        // Register the global Fluid viewhelper namespace (if specified)
        $globalNSPrefix = trim(
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']) ?
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
                                                      ->get('tw_base', 'globalNSPrefix') :
                unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['tw_base'])['globalNSPrefix']
        ) ?: 'base';
        // die($globalNSPrefix);
        if (strlen($globalNSPrefix)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$globalNSPrefix] = ['Tollwerk\\TwBase\\ViewHelpers'];
        }

        // Register a custom JavaScript & CSS resource compressors
        $GLOBALS['TYPO3_CONF_VARS']['FE']['jsConcatenateHandler']  = \Tollwerk\TwBase\Utility\ConcatenateUtility::class.'->concatenateJs';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cssConcatenateHandler'] = \Tollwerk\TwBase\Utility\ConcatenateUtility::class.'->concatenateCssFiles';

        // Register classes to be available in 'eval' of TCA
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\Tollwerk\TwBase\Evaluation\NumberEvaluation::class] = '';

        // Register a custom Query Factory
        $extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\Container\Container::class
        );
        $extbaseObjectContainer->registerImplementation(
            \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class,
            \Tollwerk\TwBase\Persistence\Generic\Storage\Typo3DbQueryParser::class
        );

        // Prepare hooks for Structured Data initialization
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['structuredData']['initialize'] = (array)($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['structuredData']['initialize'] ?? []);

        // Prepare non-breaking space replacement fields
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nbspCleanup']                 = (array)($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nbspCleanup'] ?? []);
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['nbspCleanup']['tt_content'][] = 'bodytext';

        // Register a custom form element type
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1585076933] = [
            'nodeName' => 'seoTitleElement',
            'priority' => 40,
            'class'    => \Tollwerk\TwBase\Form\Element\SeoTitleElement::class,
        ];
    }
);
