<?php

/**
 * Find the template, the resources and the examples of a component
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tollwerk\TwBase\Exception\LogicException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * Find the template, the resources and the examples of a component
 *
 * Of several packages with a template under the same name, the one with the highest priority applies, and so do its
 * resources and examples. A package that has ES modules for its components maps "Resources/Public/" or a folder
 * above the module in its "Configuration/JavaScriptModules.php", such as
 * "'@tollwerk/tw-base/' => 'EXT:tw_base/Resources/Public/'".
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final class ComponentLocator
{
    /**
     * Components found so far, by namespace and name
     *
     * @var array<string, Component|null>
     */
    private array $components = [];

    /**
     * Resources found so far, by namespace and name
     *
     * @var array<string, Resources>
     */
    private array $resources = [];

    /**
     * Imports of each package from its "Configuration/JavaScriptModules.php"
     *
     * @var array<string, array<string, string>>
     */
    private array $imports = [];

    /**
     * Constructor
     *
     * @param PackageManager $packageManager Package manager
     */
    public function __construct(private readonly PackageManager $packageManager) {}

    /**
     * Find a component
     *
     * @param ComponentTemplateResolverInterface&ViewHelperResolverDelegateInterface $collection Collection
     * @param string                                                                 $name       Name, such as
     *                                                                                           "event.teaser"
     *
     * @return Component|null The component, or null if its template is not in "Resources/Private/Components" of a
     *                        package
     */
    public function locate(
        ComponentTemplateResolverInterface&ViewHelperResolverDelegateInterface $collection,
        string $name,
    ): ?Component {
        $key = $collection->getNamespace() . ':' . $name;
        if (\array_key_exists($key, $this->components)) {
            return $this->components[$key];
        }

        $template = $collection->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat(
            'Default',
            $collection->resolveTemplateName($name),
        );
        $this->components[$key] = null === $template
            ? null
            : $this->component($collection->getNamespace(), $name, $template);

        return $this->components[$key];
    }

    /**
     * The resources of a component that exist
     *
     * @param Component $component Component
     *
     * @return Resources
     *
     * @throws LogicException If the component has an ES module its package maps in no import
     */
    public function resources(Component $component): Resources
    {
        $key = $component->namespace . ':' . $component->name;
        if (isset($this->resources[$key])) {
            return $this->resources[$key];
        }

        $critical = $component->resourceFile('.critical.css');
        $this->resources[$key] = new Resources(
            $component->packageKey . ':' . $component->path,
            is_file($component->resourceFile('.css')) ? $component->resource('.css') : null,
            is_file($critical) ? (string) file_get_contents($critical) : null,
            is_file($component->resourceFile('.js')) ? $this->specifier($component) : null,
        );

        return $this->resources[$key];
    }

    /**
     * Component of a template file
     *
     * @param string $namespace Namespace of the collection
     * @param string $name      Name of the component
     * @param string $template  Absolute path of the template
     *
     * @return Component|null Null if the template is in no folder of components of an active package
     */
    private function component(string $namespace, string $name, string $template): ?Component
    {
        $position = strrpos($template, '/' . Component::TEMPLATES);
        if (false === $position) {
            return null;
        }

        $packagePath = substr($template, 0, $position + 1);
        foreach ($this->packageManager->getActivePackages() as $package) {
            if (realpath($package->getPackagePath()) === realpath($packagePath)) {
                $path = substr($template, $position + 1 + \strlen(Component::TEMPLATES));

                return new Component(
                    $namespace,
                    $name,
                    $template,
                    $package->getPackageKey(),
                    $packagePath,
                    (string) preg_replace('/(\.fluid)?\.html$/', '', $path),
                );
            }
        }

        return null;
    }

    /**
     * Specifier of the ES module of a component in the import map
     *
     * @param Component $component Component
     *
     * @return string
     *
     * @throws LogicException If the package maps the module in no import
     */
    private function specifier(Component $component): string
    {
        $resource = $component->resource('.js');
        foreach ($this->imports($component) as $specifier => $path) {
            if (str_ends_with($specifier, '/') && str_starts_with($resource, $path)) {
                return $specifier . substr($resource, \strlen($path));
            }

            if ($path === $resource) {
                return $specifier;
            }
        }

        throw new LogicException(\sprintf(
            'The module of the component "%s" is in no import of Configuration/JavaScriptModules.php of "%s"',
            $component->name,
            $component->packageKey,
        ), 1795776001);
    }

    /**
     * Imports of the package of a component
     *
     * An import maps its specifier to a path, or to an array with the path and the files it excludes. Excluded
     * files still resolve through the prefix, so only the path counts.
     *
     * @param Component $component Component
     *
     * @return array<string, string> Paths by specifier
     */
    private function imports(Component $component): array
    {
        if (!isset($this->imports[$component->packageKey])) {
            $file = $component->packagePath . 'Configuration/JavaScriptModules.php';
            $configuration = is_file($file) ? require $file : [];
            $imports = \is_array($configuration) && \is_array($configuration['imports'] ?? null)
                ? $configuration['imports']
                : [];
            $paths = [];
            foreach ($imports as $specifier => $path) {
                $path = \is_array($path) ? $path['path'] ?? null : $path;
                if (!\is_string($specifier) || !\is_string($path)) {
                    continue;
                }

                $paths[$specifier] = $path;
            }

            $this->imports[$component->packageKey] = $paths;
        }

        return $this->imports[$component->packageKey];
    }
}
