<?php

/**
 * The JSON-LD graph of a page
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\StructuredData;

use JsonException;
use Tollwerk\TwBase\StructuredData\StructuredDataGraph;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * The JSON-LD graph of a page
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class StructuredDataGraphTest extends UnitTestCase
{
    /**
     * The first address of the page is the base of the ids, without its fragment
     *
     * @return void
     */
    public function testTheFirstAddressIsTheBase(): void
    {
        $graph = new StructuredDataGraph();
        $graph->useBaseUri('https://example.org/summit#top');
        $graph->useBaseUri('https://example.org/other');

        self::assertSame('https://example.org/summit', $graph->baseUri());
    }

    /**
     * Nodes get absolute ids, nested and appended properties, and values set before they exist
     *
     * @return void
     *
     * @throws JsonException If the graph cannot be encoded
     */
    public function testPropertiesNestAppendAndWaitForTheirNode(): void
    {
        $graph = new StructuredDataGraph();
        $graph->useBaseUri('https://example.org/summit');
        $graph->set('#event', 'location.name', 'Bonn');
        $graph->register('Event', '#event', ['@type' => 'Thing', 'name' => 'Summit']);
        $graph->add('#event', 'performer', ['@id' => '#ada']);
        $graph->add('#event', 'performer', ['@id' => '#grace']);
        $graph->set('#event', ['offers', ''], 'Ticket');
        $graph->set('#event', 'name.', 'Ignored');
        $graph->add('#event', 'location.', 'Online');

        self::assertSame(
            '{"@context":"https://schema.org","@graph":[{"@type":"Event","@id":"https://example.org/summit#event",'
            . '"name":"Summit","location":[{"name":"Bonn"},"Online"],'
            . '"performer":[{"@id":"#ada"},{"@id":"#grace"}],"offers":["Ticket"]}]}',
            $graph->toJson(),
        );
    }

    /**
     * Main entities nest, the innermost counts
     *
     * @return void
     */
    public function testMainEntitiesNest(): void
    {
        $graph = new StructuredDataGraph();
        self::assertNull($graph->mainEntity());

        $graph->useBaseUri('https://example.org/');
        $graph->pushMainEntity('#event');
        $graph->pushMainEntity('https://example.org/person');
        self::assertSame('https://example.org/person', $graph->mainEntity());
        $graph->popMainEntity();
        self::assertSame('https://example.org/#event', $graph->mainEntity());
    }

    /**
     * Nodes are found by their id, relative or absolute, and by their type
     *
     * @return void
     */
    public function testNodesAreFoundByIdAndType(): void
    {
        $graph = new StructuredDataGraph();
        self::assertTrue($graph->isEmpty());
        $graph->useBaseUri('https://example.org/');
        $graph->register('Thing', '#thing', ['name' => 'Thing']);
        $graph->register('ItemList', '#list-1');
        $graph->register('ItemList', '#list-2');

        self::assertFalse($graph->isEmpty());
        self::assertSame('Thing', $graph->get('https://example.org/#thing')['name'] ?? null);
        self::assertSame('Thing', $graph->get('#thing')['name'] ?? null);
        self::assertNull($graph->get('#other'));
        self::assertSame(['https://example.org/#list-1', 'https://example.org/#list-2'], $graph->ofType('ItemList'));
        self::assertSame([], $graph->ofType('Event'));
    }

    /**
     * The graph starts over for the next page, without nodes, entities and base
     *
     * @return void
     */
    public function testResetStartsOver(): void
    {
        $graph = new StructuredDataGraph();
        $graph->useBaseUri('https://example.org/');
        $graph->pushMainEntity('#event');
        $graph->register('Thing', '#thing');

        $graph->reset();
        self::assertTrue($graph->isEmpty());
        self::assertNull($graph->mainEntity());
        self::assertSame('', $graph->baseUri());
    }

    /**
     * The main entity of the page counts outside of the parts, and the first one stays
     *
     * @return void
     */
    public function testPageEntity(): void
    {
        $graph = new StructuredDataGraph();
        $graph->useBaseUri('https://example.org/');
        $graph->pushMainEntity('#part');
        $graph->usePageEntity('#event');
        $graph->usePageEntity('#other');
        self::assertSame('https://example.org/#part', $graph->mainEntity());
        $graph->popMainEntity();
        self::assertSame('https://example.org/#event', $graph->mainEntity());
        $graph->reset();
        self::assertNull($graph->mainEntity());
    }

    /**
     * A node registered again keeps its type and its values, and gains the properties it lacks
     *
     * @return void
     */
    public function testRegisteringAgainLosesNothing(): void
    {
        $graph = new StructuredDataGraph();
        $graph->register('Event', 'https://example.org/#event', ['name' => 'Summit']);
        $graph->set('https://example.org/#event', 'image', 'https://example.org/summit.png');
        $graph->register('Thing', 'https://example.org/#event', ['name' => 'Other', 'url' => 'https://example.org/']);

        self::assertSame(
            [
                '@type' => 'Event',
                '@id' => 'https://example.org/#event',
                'name' => 'Summit',
                'image' => 'https://example.org/summit.png',
                'url' => 'https://example.org/',
            ],
            $graph->get('https://example.org/#event'),
        );
    }

    /**
     * The JSON can be embedded in a script element
     *
     * @return void
     *
     * @throws JsonException If the graph cannot be encoded
     */
    public function testJsonIsSafeInAScriptElement(): void
    {
        $graph = new StructuredDataGraph();
        $graph->register('Thing', 'https://example.org/', ['name' => '</script> & Ä']);

        self::assertStringContainsString('"\u003C/script\u003E \u0026 Ä"', $graph->toJson());
    }
}
