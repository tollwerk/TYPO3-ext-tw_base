<?php

/**
 * What happens once a page is rendered
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\EventListener;

use InvalidArgumentException;
use JsonException;
use Psr\Log\NullLogger;
use ReflectionAttribute;
use ReflectionMethod;
use Tollwerk\TwBase\Heading\EventListener\ResetHeadingContext;
use Tollwerk\TwBase\Heading\HeadingContextManager;
use Tollwerk\TwBase\StructuredData\EventListener\EmbedStructuredData;
use Tollwerk\TwBase\StructuredData\StructuredDataGraph;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use TYPO3\CMS\Frontend\Event\BeforePageIsResolvedEvent;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * What happens once a page is rendered
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class PageGeneratedTest extends UnitTestCase
{
    /**
     * The graph goes into the head of the page, and the next page starts with an empty one
     *
     * @return void
     *
     * @throws JsonException If the graph cannot be encoded
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testStructuredDataIsEmbeddedIntoTheHead(): void
    {
        $graph = new StructuredDataGraph();
        $graph->register('WebPage', 'https://example.org/');
        $event = $this->event('<html><head><title>Page</title></HEAD><body></body></html>');
        new EmbedStructuredData($graph)($event);

        self::assertSame(
            '<html><head><title>Page</title><script type="application/ld+json">{"@context":"https://schema.org",'
            . '"@graph":[{"@type":"WebPage","@id":"https://example.org/"}]}</script></HEAD><body></body></html>',
            $event->getContent(),
        );
        self::assertTrue($graph->isEmpty());

        $fragment = $this->event('<p>No head</p>');
        $graph->register('WebPage', 'https://example.org/');
        new EmbedStructuredData($graph)($fragment);
        self::assertSame('<p>No head</p>', $fragment->getContent());
    }

    /**
     * The graph also starts over before a page is resolved, so that an error page that renders while another page
     * does neither takes its nodes nor its address
     *
     * @return void
     */
    public function testStructuredDataStartsOverBeforeAPage(): void
    {
        $graph = new StructuredDataGraph();
        $graph->useBaseUri('https://example.org/stopped');
        $graph->register('Event', '#event');
        $listener = new ReflectionMethod(EmbedStructuredData::class, 'reset');
        $events = array_map(
            static fn(ReflectionAttribute $attribute): mixed => $attribute->newInstance()->event,
            $listener->getAttributes(AsEventListener::class),
        );
        new EmbedStructuredData($graph)->reset();

        self::assertSame([BeforePageIsResolvedEvent::class], $events);
        self::assertTrue($graph->isEmpty());
        self::assertSame('', $graph->baseUri());
    }

    /**
     * The next page starts with the first heading level
     *
     * @return void
     */
    public function testHeadingLevelsStartOver(): void
    {
        $manager = new HeadingContextManager(new NullLogger());
        $manager->setUp(3);
        new ResetHeadingContext($manager)();

        self::assertSame(0, $manager->currentLevel());
    }

    /**
     * Event of a rendered page
     *
     * @param string $content Content of the page
     *
     * @return AfterCacheableContentIsGeneratedEvent
     *
     * @throws InvalidArgumentException If the request is invalid
     */
    private function event(string $content): AfterCacheableContentIsGeneratedEvent
    {
        $request = new ServerRequest('https://example.org/');

        return new AfterCacheableContentIsGeneratedEvent($request, $content, 'page', true);
    }
}
