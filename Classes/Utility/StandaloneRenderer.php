<?php

/**
 * tollwerk
 *
 * @category   Tollwerk
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
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
 *
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

namespace Tollwerk\TwBase\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException as InvalidConfigurationTypeExceptionAlias;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Standalone Renderer
 *
 * @package    Tollwerk\TwBase
 * @subpackage Tollwerk\TwBase\Utility
 */
class StandaloneRenderer
{
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * Extension configuration
     *
     * @var array
     */
    protected $configuration;
    /**
     * Partial root paths
     *
     * @var string[]
     */
    protected $partialRootPaths;
    /**
     * Template root paths
     *
     * @var string[]
     */
    protected $templateRootPaths;
    /**
     * Layout root paths
     *
     * @var string[]
     */
    protected $layoutRootPaths;
    /**
     * Controller extension name
     *
     * @var string
     */
    protected $controllerExtensionName;

    /**
     * Constructor
     *
     * @param string $controllerExtensionName Controller Extension Name
     *
     * @throws InvalidConfigurationTypeExceptionAlias
     * @throws Exception
     */
    public function __construct(string $controllerExtensionName = null)
    {
        $this->controllerExtensionName = $controllerExtensionName;
        $this->objectManager           = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager          = $this->objectManager->get(ConfigurationManager::class);
        $this->configuration           = $configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK,
            $this->controllerExtensionName
        );

        // Partial root paths
        $partialRootPaths       = empty($this->configuration['view']['partialRootPaths']) ?
            (empty($this->configuration['view']['partialRootPath']) ? [] : [$this->configuration['view']['partialRootPath']]) :
            (array)$this->configuration['view']['partialRootPaths'];
        $this->partialRootPaths = array_map([$this, 'prepareRootPath'], $partialRootPaths);
        ksort($this->partialRootPaths);

        // Template root paths
        $templateRootPaths       = empty($this->configuration['view']['templateRootPaths']) ?
            (empty($this->configuration['view']['templateRootPath']) ? [] : [$this->configuration['view']['templateRootPath']]) :
            (array)$this->configuration['view']['templateRootPaths'];
        $this->templateRootPaths = array_map([$this, 'prepareRootPath'], $templateRootPaths);
        ksort($this->templateRootPaths);

        // Layout root paths
        $layoutRootPaths       = empty($this->configuration['view']['layoutRootPaths']) ?
            (empty($this->configuration['view']['layoutRootPath']) ? [] : [$this->configuration['view']['layoutRootPath']]) :
            (array)$this->configuration['view']['layoutRootPaths'];
        $this->layoutRootPaths = array_map([$this, 'prepareRootPath'], $layoutRootPaths);
        ksort($this->layoutRootPaths);
    }

    /**
     * Refine and prepare a root path
     *
     * @param string $rootPath Root path
     *
     * @return string Refined root path
     */
    protected function prepareRootPath(string $rootPath): string
    {
        return rtrim(GeneralUtility::getFileAbsFileName($rootPath), '/');
    }

    /**
     * Render a Fluid template (alias for renderTemplate())
     *
     * @param string $templateName  Template name
     * @param array $parameters     Parameters
     * @param string $format        Optional: Template format
     * @param string|null $section  Optional: template section
     * @param string|null $language Optional: language suffix
     *
     * @return string Rendered template
     * @throws Exception
     * @throws InvalidExtensionNameException
     */
    public function render(
        string $templateName,
        array $parameters = [],
        string $format = 'html',
        string $section = null,
        string $language = null
    ): string {
        return $this->renderTemplate($templateName, $parameters, $format, $section, $language);
    }

    /**
     * Render a Fluid template
     *
     * @param string $templateName  Template name
     * @param array $parameters     Parameters
     * @param string $format        Optional: Template format
     * @param string|null $section  Optional: template section
     * @param string|null $language Optional: language suffix
     *
     * @return string Rendered template
     * @throws Exception
     * @throws InvalidExtensionNameException
     */
    public function renderTemplate(
        string $templateName,
        array $parameters = [],
        string $format = 'html',
        string $section = null,
        string $language = null
    ): string {
        return $this->renderWithRootPath(
            $this->templateRootPaths,
            $templateName,
            $parameters,
            $format,
            $section,
            $language
        );
    }

    /**
     * Render a Fluid template or partial
     *
     * @param array $rootPaths      Root path
     * @param string $name          Template or partial name
     * @param array $parameters     Parameters
     * @param string $format        Optional: Template format
     * @param string|null $section  Optional: template section
     * @param string|null $language Optional: language suffix
     *
     * @return string Rendered template
     * @throws Exception
     * @throws InvalidExtensionNameException
     * @throws InvalidTemplateResourceException
     */
    protected function renderWithRootPath(
        array $rootPaths,
        string $name,
        array $parameters,
        string $format,
        string $section = null,
        string $language = null
    ): string {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setFormat($format);
        $view->setTemplateRootPaths($this->templateRootPaths);
        $view->setLayoutRootPaths($this->layoutRootPaths);
        $view->setPartialRootPaths($this->partialRootPaths);

        // Find the first matching root path, potentially considering the given language
        $name                    = trim($name, '/');
        $language                = strtolower($language);
        $templatePathAndFilename = null;
        foreach (array_reverse($rootPaths) as $rootPath) {
            // Test whether a localized template is available
            if ($language) {
                $localizedPath = "$rootPath/$name.$language.$format";
                if (file_exists($localizedPath)) {
                    $templatePathAndFilename = $localizedPath;
                    if (!empty($GLOBALS['BE_USER']->uc)) {
                        $GLOBALS['BE_USER']->uc['lang'] = $language;
                    }
                    break;
                }
            }

            // Else: test whether the template is available
            $path = "$rootPath/$name.$format";
            if (file_exists($path)) {
                $templatePathAndFilename = $path;
                break;
            }
        }

        // Throw an error if the template resource isn't available
        if (empty($templatePathAndFilename)) {
            throw new InvalidTemplateResourceException(
                "The Fluid template file \"$name.$format\" could not be loaded.",
                1569768850
            );
        }

        // Set the controller extension name (if available)
        if ($this->controllerExtensionName) {
            $view->getRequest()->setControllerExtensionName($this->controllerExtensionName);
            LocalizationUtility::resetExtensionLanguageCache($this->controllerExtensionName);
        }

        $view->setTemplatePathAndFilename($templatePathAndFilename);
        $parameters['settings'] = $this->configuration['settings'];
        $view->assignMultiple($parameters);

        return $section ? $view->renderSection($section, $parameters) : $view->render();
    }

    /**
     * Render a Fluid partial
     *
     * @param string $partialName   Partial name
     * @param array $parameters     Parameters
     * @param string $format        Optional: Template format
     * @param string|null $section  Optional: template section
     * @param string|null $language Optional: language suffix
     *
     * @return string Rendered template
     * @throws Exception
     * @throws InvalidExtensionNameException
     */
    public function renderPartial(
        string $partialName,
        array $parameters = [],
        string $format = 'html',
        string $section = null,
        string $language = null
    ): string {
        return $this->renderWithRootPath(
            $this->partialRootPaths,
            $partialName,
            $parameters,
            $format,
            $section,
            $language
        );
    }
}
