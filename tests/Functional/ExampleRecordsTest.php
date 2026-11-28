<?php

/**
 * Tests of examples that name records and render on a page of a site
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

use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Tollwerk\TwBase\Component\Example\ExampleEndpoint;
use Tollwerk\TwBase\Component\Example\Records;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use UnexpectedValueException;

/**
 * Tests of examples that name records and render on a page of a site
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ExampleRecordsTest extends FunctionalTestCase
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
     * Pages, categories and a site in English and German
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Categories.csv');
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
              - title: Deutsch
                languageId: 1
                base: /de/
                locale: de_DE.UTF-8
                fallbackType: fallback
                fallbacks: '0'
            YAML);
        file_put_contents($sites . '/setup.typoscript', "page = PAGE\npage.10 = TEXT\npage.10.value = Page\n");
    }

    /**
     * Records become models, also in a list, and links lead to pages of the site
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws SiteNotFoundException If the site is missing
     * @throws UnexpectedValueException If an example or a record is missing
     * @throws InvalidArgumentException If an address is invalid
     * @throws DBALException If a page cannot be read
     */
    #[Test]
    public function recordsBecomeModels(): void
    {
        $uri = $this->get(ExampleEndpoint::class)->uri($this->site(), 'twc:test.category', 'default');

        self::assertSame('/', $uri->getPath());
        self::assertSame(
            '<p class="Category"><a href="/legal-notice">Accessibility</a>, Usability</p>',
            trim((string) $this->request((string) $uri)->getBody()),
        );
    }

    /**
     * An example renders on its page and in its language
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws SiteNotFoundException If the site is missing
     * @throws UnexpectedValueException If an example or a record is missing
     * @throws InvalidArgumentException If an address is invalid
     * @throws DBALException If a page cannot be read
     */
    #[Test]
    public function exampleRendersOnItsPageInItsLanguage(): void
    {
        $uri = $this->get(ExampleEndpoint::class)->uri($this->site(), 'twc:test.category', 'german');

        self::assertSame('/de/legal-notice', $uri->getPath());
        self::assertSame(
            '<p class="Category"><a href="/de/legal-notice">Barrierefreiheit</a></p>',
            trim((string) $this->request((string) $uri)->getBody()),
        );
    }

    /**
     * An example on a page that the site does not show is an error, not the start page
     *
     * @param string $example Example
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws SiteNotFoundException If the site is missing
     * @throws UnexpectedValueException If an example is missing
     * @throws DBALException If a page cannot be read
     * @throws InvalidArgumentException If an address cannot be built
     */
    #[Test]
    #[TestWith(['hiddenPage'])]
    #[TestWith(['deletedPage'])]
    #[TestWith(['unknownPage'])]
    public function examplesNeedAPageTheSiteShows(string $example): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1795776403);
        $this->get(ExampleEndpoint::class)->uri($this->site(), 'twc:test.category', $example);
    }

    /**
     * Without a valid signature, the address shows the page; an example that cannot render answers with status 404
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws SiteNotFoundException If the site is missing
     * @throws UnexpectedValueException If an example or a record is missing
     * @throws InvalidArgumentException If an address is invalid
     * @throws DBALException If a page cannot be read
     */
    #[Test]
    public function onlySignedAddressesRenderExamples(): void
    {
        $uri = $this->get(ExampleEndpoint::class)->uri($this->site(), 'twc:test.category', 'missing');
        $forged = str_replace(ExampleEndpoint::SIGNATURE . '=', ExampleEndpoint::SIGNATURE . '=0', (string) $uri);

        $missing = $this->request((string) $uri);
        self::assertSame(404, $missing->getStatusCode());
        self::assertStringContainsString('There is no record sys_category:99', (string) $missing->getBody());
        self::assertStringContainsString('<title>Home</title>', (string) $this->request($forged)->getBody());
    }

    /**
     * A table without a model, a class of another table and a language the site lacks are errors
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If a reference without a record is no longer kept
     */
    #[Test]
    public function referencesNeedAModelOfTheirTable(): void
    {
        $records = $this->get(Records::class);
        $codes = [];
        $references = [
            ['record' => 'pages', 'uid' => 1],
            ['record' => 'sys_category', 'uid' => 1, 'model' => Site::class],
            ['record' => 'sys_category', 'uid' => '1'],
        ];
        foreach ($references as $reference) {
            try {
                $records->resolve(['value' => $reference]);
            } catch (UnexpectedValueException $exception) {
                $codes[] = $exception->getCode();
            }
        }

        self::assertSame([1795776304, 1795776303, 1795776301], $codes);
        self::assertSame(['value' => ['record' => 'pages']], $records->resolve(['value' => ['record' => 'pages']]));
    }

    /**
     * Files and references to files name the class of the core
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If a file is missing
     */
    #[Test]
    public function filesOfTheCoreLoad(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Files.csv');
        $records = $this->get(Records::class);
        $resolved = $records->resolve([
            'reference' => ['record' => 'sys_file_reference', 'uid' => 1, 'model' => FileReference::class],
            'file' => ['record' => 'sys_file', 'uid' => 1, 'model' => File::class],
        ]);

        $reference = $resolved['reference'] ?? null;
        $file = $resolved['file'] ?? null;
        self::assertInstanceOf(FileReference::class, $reference);
        self::assertSame('Logo', $reference->getTitle());
        self::assertInstanceOf(File::class, $file);
        self::assertSame('logo.png', $file->getName());
        $this->expectExceptionCode(1795776302);
        $records->resolve(['file' => ['record' => 'sys_file', 'uid' => 99, 'model' => File::class]]);
    }

    /**
     * Response of the frontend to an address
     *
     * @param string $uri Absolute address
     *
     * @return ResponseInterface
     *
     * @throws InvalidArgumentException If the address is invalid
     */
    private function request(string $uri): ResponseInterface
    {
        return $this->executeFrontendSubRequest(new InternalRequest($uri));
    }

    /**
     * The site in English and German
     *
     * @return Site
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws SiteNotFoundException If the site is missing
     */
    private function site(): Site
    {
        $site = $this->get(SiteFinder::class)->getSiteByIdentifier('example');
        self::assertInstanceOf(Site::class, $site);

        return $site;
    }
}
