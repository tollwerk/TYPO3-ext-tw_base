<?php

/**
 * Each example of a component of tw_base renders with a sound structure
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

use Dom\Element;
use Dom\HTMLDocument;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Tollwerk\TwBase\Component\Example\ExampleRenderer;
use Tollwerk\TwBase\Component\Example\Examples;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use UnexpectedValueException;

/**
 * Each example of a component of tw_base renders with a sound structure
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ComponentExamplesTest extends FunctionalTestCase
{
    /**
     * Extensions under test
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $testExtensionsToLoad = ['tollwerk/tw-base'];

    /**
     * Extensions of the core that tw_base needs
     *
     * @var array<non-empty-string>
     */
    #[Override]
    protected array $coreExtensionsToLoad = ['filemetadata'];

    /**
     * Every example of every component
     *
     * @return iterable<string, array{string, string}>
     *
     * @throws UnexpectedValueException If a file with examples is invalid
     */
    public static function examples(): iterable
    {
        foreach (Examples::find(\dirname(__DIR__, 2) . '/Resources/Private/Components') as $component => $file) {
            foreach (array_keys(Examples::fromFile($file)->examples) as $example) {
                yield $component . '#' . $example => [$component, $example];
            }
        }
    }

    /**
     * Each mail example is a document with a language, a title and one heading; links have text, ids are unique
     *
     * @param string $component Component
     * @param string $example   Example
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    #[DataProvider('examples')]
    public function exampleRenders(string $component, string $example): void
    {
        $html = $this->get(ExampleRenderer::class)->render($component, $example);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        self::assertNotSame('', $document->documentElement?->getAttribute('lang') ?? '');
        self::assertNotSame('', trim($document->title));
        self::assertCount(1, $document->querySelectorAll('h1'));
        self::assertCount(0, $document->querySelectorAll('table'));
        foreach ($document->querySelectorAll('a') as $link) {
            self::assertInstanceOf(Element::class, $link);
            self::assertNotSame('', trim($link->textContent ?? ''));
            self::assertNotSame('', $link->getAttribute('href') ?? '');
        }

        $ids = array_map(
            static fn(Element $element): string => (string) $element->getAttribute('id'),
            iterator_to_array($document->querySelectorAll('[id]')),
        );
        self::assertSame(array_unique($ids), $ids);
    }
}
