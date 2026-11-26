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
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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
    protected array $testExtensionsToLoad = ['tollwerk/tw-base'];

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
        $language = new SiteLanguage(1, 'de_DE.UTF-8', new Uri('/de/'), []);
        $request = new ServerRequest('https://example.org/de/')->withAttribute('language', $language);
        $mail = $this->get(Mailer::class)->compose(
            $request,
            new Address('ada@example.org', 'Ada Lovelace'),
            'TwBase/Test',
            'Dein Code',
            ['code' => '123456'],
        );

        self::assertSame('Dein Code', $mail->getSubject());
        $recipients = array_map(static fn(Address $to): string => $to->getAddress(), $mail->getTo());
        self::assertSame(['ada@example.org'], $recipients);
        $html = (string) $mail->getHtmlBody(true);
        self::assertStringContainsString('<html lang="de-DE">', $html);
        self::assertStringContainsString('<title>Dein Code</title>', $html);
        self::assertStringContainsString('<h1 style="font-size: 1.25rem;">Dein Code</h1>', $html);
        self::assertStringContainsString('<strong>123456</strong>', $html);
        $text = (string) $mail->getTextBody(true);
        self::assertStringStartsWith('Dein Code', trim($text));
        self::assertStringContainsString('Your code is 123456.', $text);
        self::assertStringNotContainsString('<', $text);
    }
}
