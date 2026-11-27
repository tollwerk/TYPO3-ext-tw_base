<?php

/**
 * Render a component with one of its examples
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\Example;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tollwerk\TwBase\Component\Component;
use Tollwerk\TwBase\Component\ComponentLocator;
use TYPO3\CMS\Fluid\Core\Component\ComponentCollectionRegistry;
use TYPO3\CMS\Fluid\Core\Component\DeclarativeComponentCollection;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use UnexpectedValueException;

/**
 * Render a component with one of its examples, within the examples of the components it requires
 *
 * The functional tests of a package render each example with it, and so does a component library.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final readonly class ExampleRenderer
{
    /**
     * Constructor
     *
     * @param ComponentCollectionRegistry $registry Collections of components
     * @param ComponentLocator            $locator  Locator of components
     * @param RenderingContextFactory     $factory  Factory of rendering contexts
     */
    public function __construct(
        private ComponentCollectionRegistry $registry,
        private ComponentLocator $locator,
        private RenderingContextFactory $factory,
    ) {}

    /**
     * The component that applies under a name
     *
     * @param string                      $component Component with its prefix, such as "twc:mail.button"
     * @param ServerRequestInterface|null $request   Request, if any
     *
     * @return Component
     *
     * @throws UnexpectedValueException If there is no such component
     */
    public function component(string $component, ?ServerRequestInterface $request = null): Component
    {
        return $this->locate($component, $this->factory->create([], $request))[1];
    }

    /**
     * The examples of a component
     *
     * @param string                      $component Component with its prefix, such as "twc:mail.button"
     * @param ServerRequestInterface|null $request   Request, if any
     *
     * @return Examples
     *
     * @throws UnexpectedValueException If there is no such component or its examples are invalid
     */
    public function examples(string $component, ?ServerRequestInterface $request = null): Examples
    {
        return Examples::fromFile($this->component($component, $request)->examplesFile());
    }

    /**
     * Render a component with one of its examples
     *
     * @param string                      $component Component with its prefix, such as "twc:mail.button"
     * @param string                      $example   Name of the example
     * @param ServerRequestInterface|null $request   Request, if any
     *
     * @return string HTML
     *
     * @throws UnexpectedValueException If there is no such component or example, or examples are within each other
     */
    public function render(string $component, string $example, ?ServerRequestInterface $request = null): string
    {
        $context = $this->factory->create([], $request);
        if ($request instanceof ServerRequestInterface) {
            $context->setAttribute(ServerRequestInterface::class, $request);
        }

        return $this->renderWithin($component, $example, $context, null, []);
    }

    /**
     * Render an example, within the example it appears in
     *
     * @param string                           $component Component with its prefix
     * @param string                           $example   Name of the example
     * @param RenderingContextInterface        $context   Rendering context
     * @param array{0: string, 1: string}|null $inner     Slot and HTML of an example within this one
     * @param list<string>                     $seen      Examples on the way here, to stop a cycle
     *
     * @return string
     *
     * @throws UnexpectedValueException If there is no such component or example, or examples are within each other
     */
    private function renderWithin(
        string $component,
        string $example,
        RenderingContextInterface $context,
        ?array $inner,
        array $seen,
    ): string {
        $key = $component . '#' . $example;
        if (\in_array($key, $seen, true)) {
            throw new UnexpectedValueException(\sprintf('The example "%s" is within itself', $key), 1795776201);
        }

        [$collection, $located] = $this->locate($component, $context);
        $examples = Examples::fromFile($located->examplesFile());
        $configuration = $examples->examples[$example] ?? throw new UnexpectedValueException(
            \sprintf('The component "%s" has no example "%s"', $component, $example),
            1795776202,
        );

        $slots = array_map(static fn(string $html): Closure => static fn(): string => $html, $configuration->slots);
        if (null !== $inner) {
            $slots[$inner[0]] = static fn(): string => $inner[1];
        }

        $html = $collection->getComponentRenderer()->renderComponent(
            $located->name,
            $configuration->arguments,
            $slots,
            $context,
        );

        $within = $configuration->within;

        return null === $within
            ? $html
            : $this->renderWithin(
                $within->component,
                $within->example,
                $context,
                [$within->slot, $html],
                [...$seen, $key],
            );
    }

    /**
     * The collection and the component that apply under a name
     *
     * @param string                    $component Component with its prefix
     * @param RenderingContextInterface $context   Rendering context, for the prefixes
     *
     * @return array{0: DeclarativeComponentCollection, 1: Component}
     *
     * @throws UnexpectedValueException If there is no such component
     */
    private function locate(string $component, RenderingContextInterface $context): array
    {
        $colon = strpos($component, ':');
        $prefix = false === $colon ? '' : substr($component, 0, $colon);
        $name = false === $colon ? $component : substr($component, $colon + 1);
        $namespaces = $context->getViewHelperResolver()->getNamespaces()[$prefix] ?? [];
        $collections = $this->registry->getAll();
        foreach (array_reverse($namespaces) as $namespace) {
            $collection = \is_string($namespace) ? $collections[$namespace] ?? null : null;
            $located = null === $collection ? null : $this->locator->locate($collection, $name);
            if (null !== $collection && $located instanceof Component) {
                return [$collection, $located];
            }
        }

        throw new UnexpectedValueException(\sprintf('There is no component "%s"', $component), 1795776203);
    }
}
