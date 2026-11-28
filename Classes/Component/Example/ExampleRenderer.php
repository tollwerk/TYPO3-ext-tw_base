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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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
     * @param Records                     $records  Records that examples name in place of models
     * @param Context                     $context  Context, for the language of the records
     */
    public function __construct(
        private ComponentCollectionRegistry $registry,
        private ComponentLocator $locator,
        private RenderingContextFactory $factory,
        private Records $records,
        private Context $context,
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
     * @throws UnexpectedValueException If there is no such component or example, examples are within each other, or a
     *                                  record is missing
     */
    public function render(string $component, string $example, ?ServerRequestInterface $request = null): string
    {
        $context = $this->factory->create([], $request);
        if ($request instanceof ServerRequestInterface) {
            $context->setAttribute(ServerRequestInterface::class, $request);
        }

        $language = $request?->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            return $this->renderWithin($component, $example, $context);
        }

        // Records load in the language of the request, as on the website
        try {
            $previous = $this->context->getAspect('language');
        } catch (AspectNotFoundException) {
            // The context creates the language aspect when asked for it, so this does not happen
            $previous = new LanguageAspect();
        }

        $this->context->setAspect('language', LanguageAspectFactory::createFromSiteLanguage($language));

        try {
            return $this->renderWithin($component, $example, $context);
        } finally {
            $this->context->setAspect('language', $previous);
        }
    }

    /**
     * Render an example, within the examples it appears in
     *
     * The outermost example renders first, and the slot that holds an inner example renders it when the outer one
     * outputs it, as on a page. State that rendering builds, such as the levels of headings and the main entity,
     * so nests as on the website.
     *
     * @param string                    $component Component with its prefix
     * @param string                    $example   Name of the example
     * @param RenderingContextInterface $context   Rendering context
     *
     * @return string
     *
     * @throws UnexpectedValueException If there is no such component or example, examples are within each other, or a
     *                                  record is missing
     */
    private function renderWithin(string $component, string $example, RenderingContextInterface $context): string
    {
        $render = null;
        $inner = null;
        foreach ($this->chain($component, $example, $context) as [$collection, $name, $configuration, $arguments]) {
            $slots = array_map(static fn(string $html): Closure => static fn(): string => $html, $configuration->slots);
            if (null !== $inner) {
                $slots[$inner[0]] = $inner[1];
            }

            $render = static fn(): string => $collection->getComponentRenderer()->renderComponent(
                $name,
                $arguments,
                $slots,
                $context,
            );
            $inner = (null === $configuration->within) ? null : [$configuration->within->slot, $render];
        }

        return (null === $render) ? '' : $render();
    }

    /**
     * An example and the examples it is within, the requested one first, with their records loaded
     *
     * All records load before anything renders, so that a missing one is an error for the whole example.
     *
     * @param string                    $component Component with its prefix
     * @param string                    $example   Name of the example
     * @param RenderingContextInterface $context   Rendering context
     *
     * @return list<array{DeclarativeComponentCollection, string, Example, array<string, mixed>}> Collection, name of
     *                                                                                             the component,
     *                                                                                             example and arguments
     *
     * @throws UnexpectedValueException If there is no such component or example, examples are within each other, or a
     *                                  record is missing
     */
    private function chain(string $component, string $example, RenderingContextInterface $context): array
    {
        $chain = [];
        $seen = [];
        while (true) {
            $key = $component . '#' . $example;
            if (\in_array($key, $seen, true)) {
                throw new UnexpectedValueException(\sprintf('The example "%s" is within itself', $key), 1795776201);
            }

            $seen[] = $key;
            [$collection, $located] = $this->locate($component, $context);
            $configuration = Examples::fromFile($located->examplesFile())->examples[$example]
                ?? throw new UnexpectedValueException(
                    \sprintf('The component "%s" has no example "%s"', $component, $example),
                    1795776202,
                );
            $arguments = $this->records->resolve($configuration->arguments);
            $chain[] = [$collection, $located->name, $configuration, $arguments];
            if (null === $configuration->within) {
                return $chain;
            }

            $component = $configuration->within->component;
            $example = $configuration->within->example;
        }
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
