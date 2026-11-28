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
            <h1>Code</h1><p style="color: #767676">Grey</p><p class="MailCode">482916</p><img src="logo.png" alt="">
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
            <p class="MailCode">482916</p>
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

    /**
     * The contrast counts for colors that end in "a", in every notation of the settings, and a color that cannot be
     * measured is a problem of its own
     *
     * @return void
     */
    #[Test]
    public function contrastOfEveryNotation(): void
    {
        $html = <<<'HTML'
            <!DOCTYPE html><html lang="en"><head>
            <meta name="color-scheme" content="light dark"><title>Colors</title></head>
            <body style="background: #ffffff !important; color: #1a1a1a"><h1>Colors</h1>
            <p style="color: #0b4f8a">Accent</p><p style="color: #aaaaaa">Grey</p><p style="color: #7a9bda">Blue</p>
            <p style="color: rgb(170 170 170)">Rgb</p><p style="color: rgba(118, 118, 118, 1)">Opaque</p>
            <p style="color: #777777ff">Hex8</p><p style="color: #000a">Hex4</p>
            <p style="color: rgb(0 0 0 / 50%)">Half</p><p style="color: hsl(0 0% 0%)">Hsl</p>
            <div style="background: #1a1a1a"><p style="color: #2a2a2a">Dark</p></div></body></html>
            HTML;

        self::assertSame([
            'The text "Grey" has a contrast of 2.32:1 instead of 4.5:1',
            'The text "Blue" has a contrast of 2.79:1 instead of 4.5:1',
            'The text "Rgb" has a contrast of 2.32:1 instead of 4.5:1',
            'The text "Hex8" has a contrast of 4.48:1 instead of 4.5:1',
            'The contrast of the text "Hex4" cannot be checked, as "#000a" is no opaque color in hex or rgb() notation',
            'The contrast of the text "Half" cannot be checked, as "rgb(0 0 0 / 50%)" is no opaque color in hex or '
                . 'rgb() notation',
            'The contrast of the text "Hsl" cannot be checked, as "hsl(0 0% 0%)" is no opaque color in hex or rgb() '
                . 'notation',
            'The text "Dark" has a contrast of 1.21:1 instead of 4.5:1',
        ], new MailInspector()->problems($html, "Colors\n\nAccent Grey Blue Rgb Opaque Hex8 Hex4 Half Hsl Dark"));
    }
}
