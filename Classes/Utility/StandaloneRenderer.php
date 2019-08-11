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
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
     * Partial root path
     *
     * @var string
     */
    protected $partialRootPath;
    /**
     * Template root path
     *
     * @var string
     */
    protected $templateRootPath;

    /**
     * Constructor
     *
     * @throws InvalidConfigurationTypeException
     * @throws Exception
     */
    public function __construct()
    {
        $this->objectManager    = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager   = $this->objectManager->get(ConfigurationManager::class);
        $this->configuration    = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
        $this->partialRootPath  = rtrim(
            GeneralUtility::getFileAbsFileName($this->configuration['view']['partialRootPath']),
            '/'
        );
        $this->templateRootPath = rtrim(
            GeneralUtility::getFileAbsFileName($this->configuration['view']['templateRootPath']),
            '/'
        );
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
     */
    public function renderTemplate(
        string $templateName,
        array $parameters = [],
        string $format = 'html',
        string $section = null,
        string $language = null
    ): string {
        return $this->renderWithRootPath(
            $this->templateRootPath,
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
     * @param string $rootPath      Root path
     * @param string $name          Template or partial name
     * @param array $parameters     Parameters
     * @param string $format        Optional: Template format
     * @param string|null $section  Optional: template section
     * @param string|null $language Optional: language suffix
     *
     * @return string Rendered template
     * @throws Exception
     */
    protected function renderWithRootPath(
        string $rootPath,
        string $name,
        array $parameters,
        string $format,
        string $section = null,
        string $language = null
    ): string {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setFormat($format);
        $view->setTemplateRootPaths($this->configuration['view']['templateRootPaths']);
        $view->setLayoutRootPaths($this->configuration['view']['layoutRootPaths']);
        $view->setPartialRootPaths($this->configuration['view']['partialRootPaths']);

        // Try localized template path
        $templatePathAndFilename = $rootPath.'/'.trim($name, '/').'.'.$format;
        if ($language) {
            $localizedTemplatePathAndFilename = $rootPath.'/'.trim($name.'.'.strtolower($language), '/').'.'.$format;
            if (file_exists($localizedTemplatePathAndFilename)) {
                $templatePathAndFilename = $localizedTemplatePathAndFilename;
            }
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
     */
    public function renderPartial(
        string $partialName,
        array $parameters = [],
        string $format = 'html',
        string $section = null,
        string $language = null
    ): string {
        return $this->renderWithRootPath(
            $this->partialRootPath,
            $partialName,
            $parameters,
            $format,
            $section,
            $language
        );
    }
}
