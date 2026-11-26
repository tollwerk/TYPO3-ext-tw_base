<?php

/**
 * Embeds the JSON-LD graph into the head of a page before the page is cached
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\StructuredData\EventListener;

use JsonException;
use Tollwerk\TwBase\StructuredData\StructuredDataGraph;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * Embeds the JSON-LD graph into the head of a page before the page is cached
 *
 * The graph is part of the cached page, so it costs nothing on later requests. Content rendered uncached cannot add to
 * it.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[AsEventListener('tw-base/embed-structured-data')]
final readonly class EmbedStructuredData
{
    /**
     * Constructor
     *
     * @param StructuredDataGraph $graph JSON-LD graph of the page
     */
    public function __construct(private StructuredDataGraph $graph) {}

    /**
     * Embed the graph and start over for the next page
     *
     * @param AfterCacheableContentIsGeneratedEvent $event Event
     *
     * @return void
     *
     * @throws JsonException If a value of the graph cannot be encoded
     */
    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        $content = $event->getContent();
        $head = stripos($content, '</head>');
        if (!$this->graph->isEmpty() && ($head !== false)) {
            $script = '<script type="application/ld+json">' . $this->graph->toJson() . '</script>';
            $event->setContent(substr_replace($content, $script, $head, 0));
        }

        $this->graph->reset();
    }
}
