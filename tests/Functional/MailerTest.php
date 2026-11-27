<?php

/**
 * Tests of the mails of tw_base
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

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Mime\Address;
use Tollwerk\TwBase\Mail\Mailer;
use Tollwerk\TwBase\Mail\MailInspector;
use Tollwerk\TwBase\Mail\MailRegistry;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use UnexpectedValueException;

/**
 * A mail has a text part and an accessible HTML part in the language of the site
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class MailerTest extends FunctionalTestCase
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
     * Point the template root path of the test to the fixtures
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $configuration = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
        if (!\is_array($configuration)) {
            return;
        }

        $GLOBALS['TYPO3_CONF_VARS'] = array_replace_recursive($configuration, [
            'MAIL' => ['templateRootPaths' => [100 => __DIR__ . '/Fixtures/Email/']],
        ]);
    }

    /**
     * The mail has both parts, the subject as title and heading, and the language of the site
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     */
    #[Test]
    public function mailHasTextAndHtml(): void
    {
        $mail = $this->compose([]);

        self::assertSame('Dein Code', $mail->getSubject());
        $recipients = array_map(static fn(Address $to): string => $to->getAddress(), $mail->getTo());
        self::assertSame(['ada@example.org'], $recipients);
        self::assertSame([], $mail->getReplyTo());
        $html = (string) $mail->getHtmlBody();
        self::assertStringContainsString('<html lang="de-DE">', $html);
        self::assertStringContainsString('<title>Dein Code</title>', $html);
        self::assertMatchesRegularExpression('#<h1[^>]*>Dein Code</h1>#', $html);
        self::assertStringContainsString('<strong>123456</strong>', $html);
        self::assertMatchesRegularExpression('#<p class="Mail-code"[^>]*>123456</p>#', $html);
        self::assertStringContainsString('You get this email because you asked for a code.', $html);
        self::assertStringContainsString('<p class="Mail-footer-line" style="margin: 0 0 16px;">Beispiel</p>', $html);
        self::assertStringNotContainsString('href=', $html);
        $text = (string) $mail->getTextBody();
        self::assertStringStartsWith("Beispiel\n\nDein Code", trim($text));
        self::assertStringContainsString('Your code is 123456.', $text);
        self::assertStringContainsString("--\nYou get this email because you asked for a code.\n\nBeispiel", $text);
        self::assertStringNotContainsString('<', $text);
    }

    /**
     * The footer has the sender, the contact address and the legal notice of the site; replies go to the contact
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     */
    #[Test]
    public function footerHasTheSettingsOfTheSite(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');
        $mail = $this->compose([
            'sender' => 'Beispiel e. V., Hauptstraße 1, 12345 Beispielstadt',
            'contact' => 'hallo@example.org',
            'imprint' => 2,
        ]);

        $replyTo = array_map(static fn(Address $to): string => $to->toString(), $mail->getReplyTo());
        self::assertSame(['"Beispiel" <hallo@example.org>'], $replyTo);
        $html = (string) $mail->getHtmlBody();
        self::assertStringContainsString('Beispiel e. V., Hauptstraße 1, 12345 Beispielstadt', $html);
        self::assertMatchesRegularExpression(
            '#<a [^>]*href="mailto:hallo@example.org"[^>]*>Write to us: hallo@example.org</a>#',
            $html,
        );
        self::assertMatchesRegularExpression(
            '#<a [^>]*href="https://example.org/de/legal-notice"[^>]*>Legal notice</a>#',
            $html,
        );
        $text = (string) $mail->getTextBody();
        self::assertStringContainsString(
            "Beispiel e. V., Hauptstraße 1, 12345 Beispielstadt\nWrite to us: hallo@example.org\n"
            . 'Legal notice: https://example.org/de/legal-notice',
            $text,
        );
    }

    /**
     * Tokens and a stylesheet of the site change the styles, which go inline and never reach the page
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     */
    #[Test]
    public function siteChangesTheStyles(): void
    {
        $mail = $this->compose([
            'contact' => 'hallo@example.org',
            'stylesheet' => 'EXT:tw_base_components/Resources/Public/Mail.css',
            'tokens' => [
                'color' => ['accent' => '#8a0b0b', 'accentText' => '#fafafa'],
                'dark' => ['color' => ['accent' => '#ffb3b3']],
            ],
        ]);

        $html = (string) $mail->getHtmlBody();
        $link = '#<a class="Mail-link" href="mailto:[^"]+" style="color: \#8a0b0b;">#';
        self::assertMatchesRegularExpression($link, $html);
        self::assertMatchesRegularExpression('#<h1 class="Mail-title" style="[^"]*color: \#8a0b0b;[^"]*">#', $html);
        self::assertStringContainsString('color: #ffb3b3 !important;', $html);
        self::assertStringNotContainsString('var(', $html);
        self::assertSame([], $this->get(AssetCollector::class)->getStyleSheets());
    }

    /**
     * The registry has the mails of all extensions, and each example composes an accessible mail
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function registeredExampleIsAccessible(): void
    {
        $mail = $this->get(MailRegistry::class)->get('tw_base_components.code');
        self::assertSame('TwBase/Test', $mail->template);
        self::assertSame(['tw_base_components.code'], array_keys($this->get(MailRegistry::class)->all()));

        $request = new ServerRequest('https://example.org/');
        $composed = $this->get(Mailer::class)->composeExample($request, 'tw_base_components.code', 'default');
        self::assertSame('Your code', $composed->getSubject());
        self::assertSame([], $this->get(MailInspector::class)->inspect($composed));
    }

    /**
     * Unknown mails and examples are errors
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function unknownExampleIsAnError(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1795776307);
        $request = new ServerRequest('https://example.org/');
        $this->get(Mailer::class)->composeExample($request, 'tw_base_components.code', 'missing');
    }

    /**
     * Unknown mails are errors
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws UnexpectedValueException If an example or mail is missing
     */
    #[Test]
    public function unknownMailIsAnError(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1795776306);
        $this->get(MailRegistry::class)->get('tw_base_components.missing');
    }

    /**
     * Compose the mail of the fixture in German, for a site with mail settings
     *
     * @param array<string, mixed> $settings Settings "twBase.mail.*"
     *
     * @return FluidEmail
     *
     * @throws ContainerExceptionInterface If a service is missing
     * @throws InvalidArgumentException If the address of the request is invalid
     */
    private function compose(array $settings): FluidEmail
    {
        $configuration = [
            'base' => 'https://example.org/',
            'websiteTitle' => 'Beispiel',
            'languages' => [
                ['languageId' => 0, 'locale' => 'en_US.UTF-8', 'base' => '/', 'title' => 'English'],
                ['languageId' => 1, 'locale' => 'de_DE.UTF-8', 'base' => '/de/', 'title' => 'Deutsch'],
            ],
        ];
        $siteSettings = SiteSettings::createFromSettingsTree(['twBase' => ['mail' => $settings]]);
        $site = new Site('example', 1, $configuration, $siteSettings);
        $request = new ServerRequest('https://example.org/de/')
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getLanguageById(1));

        return $this->get(Mailer::class)->compose(
            $request,
            new Address('ada@example.org', 'Ada Lovelace'),
            'TwBase/Test',
            'Dein Code',
            ['code' => '123456'],
        );
    }
}
