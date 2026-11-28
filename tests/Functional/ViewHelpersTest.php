<?php

/**
 * The ViewHelpers of tw_base in templates
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
use Exception;
use GdImage;
use InvalidArgumentException;
use JsonException;
use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tollwerk\TwBase\Link\LinkInfoFactory;
use Tollwerk\TwBase\StructuredData\StructuredDataGraph;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * The ViewHelpers of tw_base in templates
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ViewHelpersTest extends FunctionalTestCase
{
    /**
     * Extensions under test
     *
     * @var non-empty-string[]
     */
    #[Override]
    protected array $testExtensionsToLoad = ['tollwerk/tw-base'];

    /**
     * System extensions tw_base needs
     *
     * @var non-empty-string[]
     */
    #[Override]
    protected array $coreExtensionsToLoad = ['filemetadata'];

    /**
     * Images are processed with ImageMagick, as in production and in the pipeline, and scaled up as TYPO3 does by
     * default; the path leads to a wrapper that can fail to write a format
     *
     * @var array<string, mixed>
     */
    #[Override]
    protected array $configurationToUseInTestInstance = [
        'GFX' => ['processor' => 'ImageMagick', 'processor_path' => __DIR__ . '/Fixtures/ImageMagick/'],
    ];

    /**
     * Forget the request of the test, which tests that run after it in the same process would take as theirs
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    /**
     * Headings get their levels from how they nest, and contexts restore them
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testHeadingsFollowTheNesting(): void
    {
        $page = $this->document(<<<'HTML'
            <base:heading content="{title}">
                <base:heading content="Section">
                    <f:variable name="marker" value="{base:heading.context.get()}" />
                    <base:heading content="Sub" class="Extra">
                        <p id="next">{base:heading.level.next()} {base:heading.type.get()}</p>
                    </base:heading>
                    <base:heading.context.restore context="{marker}" />
                    <base:heading content="Sibling" />
                    <base:heading.context.shift by="1"><base:heading content="Shifted" /></base:heading.context.shift>
                    <base:heading.context.shift><base:heading content="Unshifted" /></base:heading.context.shift>
                    <base:heading content="Hidden" hidden="1" />
                    <base:heading content="Deep" level="8" restoreContext="0" />
                </base:heading>
            </base:heading>
            HTML, ['title' => 'The <code>picture</code> element, <span lang="fr">s’il vous plaît</span>']);

        $title = $page->querySelector('h1.Heading--xx-large');
        self::assertSame('picture', $title?->querySelector('code')?->textContent);
        self::assertSame('fr', $title->querySelector('span')?->getAttribute('lang'));
        self::assertSame('Section', $page->querySelector('h2')?->textContent);
        self::assertSame('Heading Heading--large Extra', $page->querySelector('h3')?->getAttribute('class'));
        self::assertSame('4 3', $page->getElementById('next')?->textContent);
        self::assertSame(
            ['Sub', 'Sibling', 'Unshifted', 'Hidden'],
            array_map(
                static fn(Element $heading): string => $heading->textContent ?? '',
                iterator_to_array($page->querySelectorAll('h3')),
            ),
        );
        self::assertSame('Shifted', $page->querySelector('h4')?->textContent);
        self::assertSame('Hidden', $page->querySelector('.Heading--hidden')?->textContent);

        $deep = $page->querySelector('[role="heading"]');
        self::assertSame('8', $deep?->getAttribute('aria-level'));
        self::assertSame('Heading Heading--x-small Heading--skipped', $deep->getAttribute('class'));
    }

    /**
     * A shift after a restore to the start of the page keeps the first level, so no second h1 follows it
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testShiftAfterAnEmptyMarker(): void
    {
        $page = $this->document(<<<'HTML'
            <base:heading content="Title" />
            <base:heading.context.restore context="" />
            <base:heading.context.shift by="1"><p>Accordion</p></base:heading.context.shift>
            <base:heading content="After" />
            HTML);

        self::assertSame('After', $page->querySelector('h2')?->textContent);
        self::assertCount(1, $page->querySelectorAll('h1'));
    }

    /**
     * A content element that fails closes its heading, shift and entity, as TYPO3 renders the rest of the page in
     * production
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testFailingContentLeavesNoState(): void
    {
        try {
            $this->render(<<<'HTML'
                <base:heading content="Broken">
                    <base:heading.context.shift by="1">
                        <base:structuredData.entityContext.wrap id="#broken">
                            <f:render partial="Missing" />
                        </base:structuredData.entityContext.wrap>
                    </base:heading.context.shift>
                </base:heading>
                HTML);
            self::fail('The missing partial is an error');
        } catch (Exception) {
            // Expected, as in a content element whose error TYPO3 shows
        }

        $page = $this->document('<base:heading content="Next" /><p>{base:structuredData.entityContext.get()}</p>');

        // As after the section of a heading of the first level that rendered
        self::assertSame('Next', $page->querySelector('h2')?->textContent);
        self::assertSame('', $page->querySelector('p')?->textContent);
    }

    /**
     * Templates build one graph; ids are relative to the page or the site
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     * @throws JsonException If the graph cannot be encoded
     */
    public function testTemplatesBuildOneGraph(): void
    {
        $output = $this->render(<<<'HTML'
            <base:structuredData.set id="#event" key="name" value="Summit" />
            <base:structuredData.register type="Event" id="#event" data="{url: 'https://example.org/'}" />
            <base:structuredData.entityContext.wrap id="#event">
                <base:structuredData.add id="{base:structuredData.entityContext.get()}" key="performer"
                    value="{base:structuredData.idref(id: '#ada', global: 1, object: 1)}" />
                <p>{base:structuredData.entityContext.get()}</p>
            </base:structuredData.entityContext.wrap>
            <p>{base:structuredData.idref()} {base:structuredData.idref(id: '#event')}</p>
            HTML);

        self::assertStringContainsString('<p>http://localhost/summit#event</p>', $output);
        self::assertStringContainsString('<p>http://localhost/summit http://localhost/summit#event</p>', $output);
        self::assertSame(
            '{"@context":"https://schema.org","@graph":[{"@type":"Event","@id":"http://localhost/summit#event",'
            . '"url":"https://example.org/","name":"Summit","performer":[{"@id":"http://localhost/summit#ada"}]}]}',
            $this->get(StructuredDataGraph::class)->toJson(),
        );
    }

    /**
     * Link targets are resolved into their parts
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testLinkTargetsResolveIntoTheirParts(): void
    {
        $output = $this->render(<<<'HTML'
            <f:for each="{links}" as="link">{f:variable(name: 'info', value: '{base:link.info(typolink: link)}')}
            [{info.type}|{info.host}|{info.email}|{info.phone}|{info.page}|{info.external ? 'external' : ''}]
            </f:for>
            HTML, ['links' => [
            'https://www.example.org/path _blank',
            'mailto:ada@example.org',
            'tel:+49123',
            't3://page?uid=7',
            '',
            't3://unknown?uid=1',
        ]]);

        self::assertStringContainsString('[url|www.example.org||||external]', $output);
        self::assertStringContainsString('[email|example.org|ada@example.org|||]', $output);
        self::assertStringContainsString('[telephone|||+49123||]', $output);
        self::assertStringContainsString('[page||||7|]', $output);
        self::assertStringContainsString('[none|||||]', $output);
        self::assertStringContainsString('[unknown|||||]', $output);
    }

    /**
     * An absolute address of the site, of any of its languages, is no external link
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     */
    public function testOwnAddressesAreNotExternal(): void
    {
        $site = new Site('example', 1, [
            'base' => 'https://example.org/',
            'languages' => [
                ['languageId' => 0, 'locale' => 'en_US.UTF-8', 'base' => '/', 'title' => 'English'],
                ['languageId' => 1, 'locale' => 'de_DE.UTF-8', 'base' => 'https://example.de/', 'title' => 'Deutsch'],
            ],
        ]);
        $links = $this->get(LinkInfoFactory::class);

        self::assertFalse($links->create('https://example.org/contact', $site)->isExternal());
        self::assertFalse($links->create('https://EXAMPLE.de/kontakt', $site)->isExternal());
        self::assertTrue($links->create('https://www.example.org/contact', $site)->isExternal());
        self::assertTrue($links->create('https://example.org/contact')->isExternal());
    }

    /**
     * A licence that is no proper name is named in the language of the page
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testAttributionInTheLanguageOfThePage(): void
    {
        $image = $this->file('rights.png');
        $image->getMetaData()->add(['creator' => 'Ada', 'tx_twbase_license' => 'all'])->save();
        $german = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('/de/'), ['title' => 'Deutsch']);
        $source = '<f:variable name="attribution" value="{base:file.attribution(file: image)}" />'
            . '<p>{attribution.licenseName}</p>';

        self::assertSame('<p>All rights reserved</p>', trim($this->render($source, ['image' => $image])));
        self::assertSame('<p>Alle Rechte vorbehalten</p>', trim($this->render($source, ['image' => $image], $german)));
    }

    /**
     * Pictures offer modern formats in several widths, with attribution from the metadata
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testPictureAndAttribution(): void
    {
        $image = $this->file('picture.png');
        $image->getMetaData()
            ->add(['alternative' => 'A test', 'creator' => 'Ada', 'tx_twbase_license' => 'BY'])
            ->save();
        $page = $this->document(<<<'HTML'
            <base:picture image="{image}" srcset="200w, 400w, 1600w" sizes="50vw" class="Photo"
                formats="avif, gif, webp" />
            <f:variable name="attribution" value="{base:file.attribution(file: image)}" />
            <p>{attribution.creator}, {attribution.licenseName}</p>
            HTML, ['image' => $image]);

        $sources = $page->querySelectorAll('picture source');
        self::assertCount(2, $sources);
        self::assertSame('image/avif', $sources->item(0)?->getAttribute('type'));
        self::assertStringContainsString('.avif 400w', (string) $sources->item(0)->getAttribute('srcset'));
        self::assertSame('image/webp', $sources->item(1)?->getAttribute('type'));

        $img = $page->querySelector('picture img');
        self::assertSame('A test', $img?->getAttribute('alt'));
        self::assertSame('lazy', $img->getAttribute('loading'));
        self::assertSame('Photo', $img->getAttribute('class'));
        self::assertSame('800', $img->getAttribute('width'));
        self::assertSame('600', $img->getAttribute('height'));
        self::assertStringContainsString('800w', (string) $img->getAttribute('srcset'));
        self::assertSame('50vw', $img->getAttribute('sizes'));
        self::assertStringContainsString('200w', (string) $img->getAttribute('srcset'));
        self::assertSame('Ada, CC BY 4.0', trim((string) $page->querySelector('p')?->textContent));
    }

    /**
     * A format that TYPO3 cannot write gets no source, instead of one with the original or another format
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testPictureLeavesOutFormatsThatFail(): void
    {
        // TYPO3 runs ImageMagick with Symfony Process, which passes $_ENV on to it
        $_ENV['TW_BASE_FAILING_FORMAT'] = 'avif';
        try {
            $page = $this->document('<base:picture image="{image}" srcset="200w, 400w" />', [
                'image' => $this->file('failing.png'),
            ]);
        } finally {
            unset($_ENV['TW_BASE_FAILING_FORMAT']);
        }

        $sources = $page->querySelectorAll('picture source');
        self::assertCount(1, $sources);
        self::assertSame('image/webp', $sources->item(0)?->getAttribute('type'));
    }

    /**
     * A file that is missing renders nothing, as with f:image
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testMissingPictureRendersNothing(): void
    {
        $image = $this->file('missing.png');
        $image->setMissing(true);

        self::assertSame('', trim($this->render('<base:picture image="{image}" />', ['image' => $image])));
    }

    /**
     * Vector graphics render as they are
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    public function testVectorGraphics(): void
    {
        $page = $this->document('<base:picture image="{image}" alt="" />', ['image' => $this->file('logo.svg')]);

        self::assertCount(0, $page->querySelectorAll('source'));
        $img = $page->querySelector('img');
        self::assertSame('', $img?->getAttribute('alt'));
        self::assertStringEndsWith('/logo.svg', (string) $img->getAttribute('src'));
    }

    /**
     * Render a template
     *
     * @param string               $source    Template source
     * @param array<string, mixed> $variables Variables
     * @param SiteLanguage|null    $language  Language of the page, if it has one
     *
     * @return string
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    private function render(string $source, array $variables = [], ?SiteLanguage $language = null): string
    {
        $request = $this->request($language);
        $context = $this->get(RenderingContextFactory::class)->create([], $request);
        $context->setAttribute(ServerRequestInterface::class, $request);
        $context->getTemplatePaths()->setTemplateSource($source);
        $view = new TemplateView($context);
        $view->assignMultiple($variables);
        $output = $view->render();
        self::assertIsString($output);

        return $output;
    }

    /**
     * Request of a page in the frontend, which is also the request of the test
     *
     * @param SiteLanguage|null $language Language of the page, if it has one
     *
     * @return ServerRequestInterface
     *
     * @throws InvalidArgumentException If the request is invalid
     */
    private function request(?SiteLanguage $language = null): ServerRequestInterface
    {
        $request = new ServerRequest('http://localhost/summit')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        if ($language instanceof SiteLanguage) {
            $request = $request->withAttribute('language', $language);
        }

        $GLOBALS['TYPO3_REQUEST'] = $request;

        return $request;
    }

    /**
     * Render a template into a document
     *
     * @param string               $source    Template source
     * @param array<string, mixed> $variables Variables
     *
     * @return HTMLDocument
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    private function document(string $source, array $variables = []): HTMLDocument
    {
        return HTMLDocument::createFromString('<!doctype html>' . $this->render($source, $variables), LIBXML_NOERROR);
    }

    /**
     * A file in fileadmin
     *
     * @param string $name Name; PNG files are an image of 800 by 600 pixels, SVG files a square
     *
     * @return File
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the request is invalid
     */
    private function file(string $name): File
    {
        $this->request();
        $this->get(StorageRepository::class)->createLocalStorage('fileadmin', 'fileadmin/', 'relative', '', true);
        $path = $this->instancePath . '/fileadmin/' . $name;
        if (str_ends_with($name, '.svg')) {
            file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"/>');
        } else {
            $image = imagecreatetruecolor(800, 600);
            self::assertInstanceOf(GdImage::class, $image);
            imagepng($image, $path);
        }

        $file = $this->get(ResourceFactory::class)->getFileObjectFromCombinedIdentifier('1:/' . $name);
        self::assertInstanceOf(File::class, $file);

        return $file;
    }
}
