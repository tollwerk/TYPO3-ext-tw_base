<?php

/**
 * Tests of the import map of pages with components that have JavaScript
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
 * Tests of the import map of pages with components that have JavaScript
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ImportMapTest extends FunctionalTestCase
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
     * A site whose page renders a component with JavaScript
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
            page.10.template.value = <ul><twc:test.item title="Item" /></ul>
            TYPOSCRIPT);
    }

    /**
     * The import map has the prefix of tw_base, which the modules of the components import enhance() from, even if
     * the package of the component does not name tw_base as a dependency
     *
     * @return void
     *
     * @throws JsonException If the import map is no JSON
     * @throws InvalidArgumentException If the address of the request is invalid
     */
    #[Test]
    public function importMapHasTheRuntime(): void
    {
        $html = (string) $this->executeFrontendSubRequest(new InternalRequest('https://example.org/'))->getBody();
        $map = HTMLDocument::createFromString($html, LIBXML_NOERROR)->querySelector('script[type="importmap"]');
        self::assertNotNull($map);
        $imports = json_decode((string) $map->textContent, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($imports);
        self::assertIsArray($imports['imports'] ?? null);
        $prefixes = array_keys($imports['imports']);

        self::assertContains('@tollwerk/tw-base/', $prefixes);
        self::assertContains('@tollwerk/tw-base-components/', $prefixes);
    }
}
