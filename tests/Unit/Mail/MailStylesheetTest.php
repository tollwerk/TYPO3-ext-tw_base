<?php

/**
 * Tests of the stylesheet of a mail
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
use Tollwerk\TwBase\Mail\MailStylesheet;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Tests of the stylesheet of a mail
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class MailStylesheetTest extends UnitTestCase
{
    /**
     * Rules go inline, at-rules of any depth into the head, with strings and escapes as they are
     *
     * @return void
     */
    #[Test]
    public function atRulesOfAnyDepthGoIntoTheHead(): void
    {
        $css = <<<'CSS'
            /* Comment with } and { */
            .Note { color: #1a1a1a; }
            .Quote::before { content: "}\201C"; }
            @charset "utf-8";
            @media (prefers-color-scheme: dark) {
                @supports (color: color-mix(in srgb, red, blue)) {
                    .Note { color: color-mix(in srgb, #fff, #000 10%); }
                }
                .Quote::before { content: '\00a0{'; }
            }
            CSS;
        $stylesheet = new MailStylesheet($css);

        self::assertSame(".Note { color: #1a1a1a; }\n.Quote::before { content: \"}\\201C\"; }", $stylesheet->inline);
        self::assertStringStartsWith('@media (prefers-color-scheme: dark) {', $stylesheet->head);
        self::assertStringContainsString('@supports (color: color-mix(in srgb, red, blue)) {', $stylesheet->head);
        self::assertStringContainsString('.Note { color: color-mix(in srgb, #fff, #000 10%); }', $stylesheet->head);
        self::assertStringContainsString(".Quote::before { content: '\\00a0{'; }", $stylesheet->head);
        self::assertStringNotContainsString('@charset', $stylesheet->head);
        self::assertSame(1, substr_count($stylesheet->head, '@media'));
    }

    /**
     * Tokens come from ":root" at the top level, below the given ones; an at-rule resolves its own over them
     *
     * @return void
     */
    #[Test]
    public function atRulesResolveTheirOwnTokens(): void
    {
        $css = <<<'CSS'
            :root { --site-note: #fff8c4; --site-text: var(--mail-color-text) }
            :root{--site-border:#ccc}
            .Note { background: var(--site-note); color: var(--site-text); border-color: var(--site-border); }
            @media (prefers-color-scheme: dark) {
                :root { --site-note: #3a3000 }
                .Note { background: var(--site-note) !important; }
            }
            @media print {
                :root { --site-print: #000; }
            }
            CSS;
        $stylesheet = new MailStylesheet($css, ['mail-color-text' => '#1a1a1a']);

        self::assertSame(
            '.Note { background: #fff8c4; color: #1a1a1a; border-color: #ccc; }',
            $stylesheet->inline,
        );
        self::assertSame(
            "@media (prefers-color-scheme: dark) {\n.Note { background: #3a3000 !important; }\n}",
            $stylesheet->head,
        );
    }

    /**
     * Given tokens win over ":root", and a token without a value keeps its fallback or stays as it is
     *
     * @return void
     */
    #[Test]
    public function givenTokensWinAndFallbacksApply(): void
    {
        $css = ':root { --mail-color-accent: #0b4f8a; } a { color: var(--mail-color-accent); }'
            . ' p { color: var(--unknown, #333); margin: var(--missing); }';
        $stylesheet = new MailStylesheet($css, ['mail-color-accent' => '#8a0b0b']);

        self::assertSame("a { color: #8a0b0b; }\np { color: #333; margin: var(--missing); }", $stylesheet->inline);
        self::assertSame('', $stylesheet->head);
    }
}
