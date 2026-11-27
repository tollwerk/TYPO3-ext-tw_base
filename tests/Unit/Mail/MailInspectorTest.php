<?php

/**
 * The inspector finds what keeps a mail from being accessible
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\Mail;

use PHPUnit\Framework\Attributes\Test;
use Tollwerk\TwBase\Mail\MailInspector;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * The inspector finds what keeps a mail from being accessible
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class MailInspectorTest extends UnitTestCase
{
    /**
     * A sound mail has no problems
     *
     * @return void
     */
    #[Test]
    public function soundMailHasNoProblems(): void
    {
        $html = <<<'HTML'
            <!DOCTYPE html><html lang="en"><head>
            <meta name="color-scheme" content="light dark"><title>Code</title></head>
            <body style="background: #fff; color: #1a1a1a">
            <h1>Code</h1><p style="color: #767676">Grey</p><p class="Mail-code">482916</p><img src="logo.png" alt="">
            <!--[if mso]><table role="presentation"><tr><td><![endif]-->
            <a href="https://example.org/account">Open your account</a><a href="mailto:hi@example.org">Write to us</a>
            <!--[if mso]></td></tr></table><![endif]--></body></html>
            HTML;
        $text = "Code\n\n482916\n\nOpen your account: https://example.org/account\nWrite to us: hi@example.org";

        self::assertSame([], new MailInspector()->problems($html, $text));
    }

    /**
     * Each problem is found
     *
     * @return void
     */
    #[Test]
    public function problemsAreFound(): void
    {
        $html = <<<'HTML'
            <html><head></head><body><h1>A</h1><h1>B</h1><table><tr><td><img src="logo.png"></td></tr></table>
            <a href="https://example.org/a">Click here</a><a href="https://example.org/b"> </a>
            <p class="Mail-code">482916</p>
            <div style="background: #ffffff"><p style="color: #999999">Pale</p>
            <h2 style="color: #949494; font-size: 24px">Large</h2>
            <p style="color: var(--x)">Token</p></div></body></html>
            HTML;

        self::assertSame([
            'The HTML has no language',
            'The HTML has no title',
            'The HTML declares no color scheme',
            'The HTML has 2 headings of the first level instead of one',
            'The HTML has tables, which mails do not need for layout',
            'The image "logo.png" has no alternative text',
            'The text "Click here" of the link to "https://example.org/a" says nothing about where it leads',
            'The text part lacks "https://example.org/a"',
            'The link to "https://example.org/b" has no text',
            'The text part lacks "https://example.org/b"',
            'The text part lacks the code "482916"',
            'The HTML has tokens without a value',
            'The text "Pale" has a contrast of 2.85:1 instead of 4.5:1',
            'The text part has markup',
        ], new MailInspector()->problems($html, 'See <b>this</b>'));
        self::assertContains('The text part is empty', new MailInspector()->problems($html, ' '));
    }
}
