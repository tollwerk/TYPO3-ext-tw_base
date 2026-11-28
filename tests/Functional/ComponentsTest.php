<?php

/**
 * Components find their template, resources and examples in the package that applies
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Functional;

use Override;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Tollwerk\TwBase\Component\EventListener\RegisterResources;
use Tollwerk\TwBase\Component\Example\ExampleRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;
use UnexpectedValueException;

/**
 * Components find their template, resources and examples in the package that applies
 *
 * The fixture "tw_base_components" registers its components with priority 90, above tw_base.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ComponentsTest extends FunctionalTestCase
{
    /**
     * Extensions under test
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $testExtensionsToLoad = [
        'tollwerk/tw-base',
        __DIR__ . '/Fixtures/Extensions/tw_base_components',
    ];

    /**
     * Extensions of the core that tw_base needs
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $coreExtensionsToLoad = ['filemetadata'];

    /**
     * An example renders within the example it requires, with its arguments and slots
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function exampleRendersWithinTheComponentItRequires(): void
    {
        $html = $this->get(ExampleRenderer::class)->render('twc:test.item', 'done');

        self::assertSame(
            '<ul class="List" aria-label="Tasks"><li class="Item Item--done">Write code <em>yesterday</em></li></ul>',
            preg_replace('/>\s+</', '><', trim($html)),
        );
        $examples = $this->get(ExampleRenderer::class)->examples('twc:test.item');
        self::assertSame(['twc:test.list'], $examples->requires);
        self::assertSame(['open', 'done'], array_keys($examples->examples));
        self::assertSame('States', ($examples->examples['open'] ?? null)?->group);
    }

    /**
     * Each rendered component registers its stylesheet, its CSS for the head and its module once; the stylesheet of the
     * page comes first
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function renderingRegistersTheResources(): void
    {
        $renderer = $this->get(ExampleRenderer::class);
        $renderer->render('twc:test.item', 'open');
        $renderer->render('twc:test.item', 'done');
        $renderer->render('twc:test.page', 'default');
        $assets = $this->get(AssetCollector::class);

        $styleSheets = $this->sources($assets->getStyleSheets(false));

        $public = 'EXT:tw_base_components/Resources/Public/Components/';
        self::assertSame([
            'tw_base_components:Test/Item/Item' => $public . 'Test/Item/Item.css',
            'tw_base_components:Test/List/List' => $public . 'Test/List/List.css',
        ], $styleSheets);
        self::assertSame(
            ['tw_base_components:Test/Page/Page' => $public . 'Test/Page/Page.css'],
            $this->sources($assets->getStyleSheets(true)),
        );
        $critical = $this->sources($assets->getInlineStyleSheets(true));
        self::assertSame(['tw_base_components:Test/Item/Item'], array_keys($critical));
        self::assertStringContainsString('.Item--done', $critical['tw_base_components:Test/Item/Item'] ?? '');
        self::assertSame(
            [RegisterResources::RUNTIME, '@tollwerk/tw-base-components/Components/Test/Item/Item.js'],
            array_values($assets->getJavaScriptModules()),
        );
    }

    /**
     * A package with a higher priority replaces a component, and its examples apply
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function higherPriorityReplacesAComponent(): void
    {
        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource('<twc:mail.button href="https://example.org/" label="Go" />');
        self::assertSame(
            '<a class="Overridden" href="https://example.org/">Go</a>',
            trim($this->string(new TemplateView($context)->render())),
        );

        $component = $this->get(ExampleRenderer::class)->component('twc:mail.button');
        self::assertSame('tw_base_components', $component->packageKey);
        $this->expectException(UnexpectedValueException::class);
        $this->get(ExampleRenderer::class)->render('twc:mail.button', 'default');
    }

    /**
     * Unknown components and examples are errors
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function unknownExampleIsAnError(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1795776202);
        $this->get(ExampleRenderer::class)->render('twc:test.item', 'missing');
    }

    /**
     * Unknown components are errors
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function unknownComponentIsAnError(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1795776203);
        $this->get(ExampleRenderer::class)->render('twc:test.missing', 'default');
    }

    /**
     * Sources of assets by their identifier
     *
     * @param array<array-key, mixed> $assets Assets of the asset collector
     *
     * @return array<string, string>
     */
    private function sources(array $assets): array
    {
        $sources = [];
        foreach ($assets as $identifier => $asset) {
            $sources[(string) $identifier] = \is_array($asset) ? $this->string($asset['source'] ?? '') : '';
        }

        return $sources;
    }

    /**
     * A value that must be a string
     *
     * @param mixed $value Value
     *
     * @return string
     */
    private function string(mixed $value): string
    {
        self::assertIsString($value);

        return $value;
    }
}
