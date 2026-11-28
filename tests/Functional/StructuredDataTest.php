<?php

/**
 * Tests of the structured data of pages in the frontend
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

use Dom\HTMLDocument;
use InvalidArgumentException;
use JsonException;
use Override;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests of the structured data of pages in the frontend
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class StructuredDataTest extends FunctionalTestCase
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
     * A site whose pages register themselves as WebPage
     *
     * @return void
     *
     * @throws RuntimeException If the folder of the site cannot be created
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');
        $sites = $this->instancePath . '/typo3conf/sites/example';
        GeneralUtility::mkdir_deep($sites);
        file_put_contents($sites . '/config.yaml', <<<'YAML'
            rootPageId: 1
            base: 'https://example.org/'
            languages:
              - title: English
                languageId: 0
                base: /
                locale: en_US.UTF-8
            YAML);
        file_put_contents($sites . '/setup.typoscript', <<<'TYPOSCRIPT'
            page = PAGE
            page.10 = FLUIDTEMPLATE
            page.10.template = TEXT
            page.10.template.value = <base:structuredData.register type="WebPage" id="#webpage" />
            TYPOSCRIPT);
    }

    /**
     * Ids come from the one address of the page, not from the address of the first request, which the page cache
     * keeps for all later ones
     *
     * @return void
     *
     * @throws InvalidArgumentException If an address is invalid
     * @throws JsonException If the graph is no JSON
     */
    #[Test]
    public function idsFollowTheAddressOfThePage(): void
    {
        $first = $this->graph('https://example.org/legal-notice?utm_source=newsletter&gclid=ABC');
        $later = $this->graph('https://example.org/legal-notice');

        $nodes = $first['@graph'] ?? null;
        self::assertIsArray($nodes);
        self::assertSame([['@type' => 'WebPage', '@id' => 'https://example.org/legal-notice#webpage']], $nodes);
        self::assertSame($first, $later);
    }

    /**
     * The JSON-LD graph of a page
     *
     * @param string $uri Address of the page
     *
     * @return array<array-key, mixed> Graph
     *
     * @throws InvalidArgumentException If the address is invalid
     * @throws JsonException If the graph is no JSON
     */
    private function graph(string $uri): array
    {
        $html = (string) $this->executeFrontendSubRequest(new InternalRequest($uri))->getBody();
        $script = HTMLDocument::createFromString($html, LIBXML_NOERROR)
            ->querySelector('script[type="application/ld+json"]');
        self::assertNotNull($script);
        $graph = json_decode((string) $script->textContent, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($graph);

        return $graph;
    }
}
