<?php

/**
 * Stylesheet of a mail, split into rules for the style attributes and rules for the head
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Mail;

/**
 * Stylesheet of a mail, split into rules for the style attributes and rules for the head
 *
 * The stylesheet finds its blocks by counting braces, outside of strings, so that blocks of any depth and values
 * such as content: "\201C" survive. CssToInlineStyles finds media with a regular expression that misses a third
 * level of braces, and no other CSS parser is a dependency of the extension.
 *
 * Rules at the top level go into the style attributes. At-rules, such as "@media" for the dark mode, go into the
 * head, as no style attribute can hold them. Tokens come from ":root" at the top level, below the given ones, and a
 * block of an at-rule resolves its own ":root" over them.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class MailStylesheet
{
    /**
     * Passes of resolving tokens, for tokens that refer to tokens
     *
     * @var integer
     */
    private const int DEPTH = 5;

    /**
     * Rules for the style attributes
     *
     * @var string
     */
    public string $inline;

    /**
     * Rules for the head, such as "@media"
     *
     * @var string
     */
    public string $head;

    /**
     * Constructor
     *
     * @param string                $css    CSS
     * @param array<string, string> $tokens Tokens without the dashes, which win over those in ":root"
     */
    public function __construct(string $css, array $tokens = [])
    {
        $statements = $this->statements((string) preg_replace('#/\*.*?\*/#s', '', $css));
        $tokens = [...$this->tokens($statements), ...$tokens];
        $inline = [];
        $head = [];
        foreach ($statements as [$prelude, $body]) {
            if ($this->isRoot($prelude)) {
                continue;
            }

            if (!str_starts_with($prelude, '@')) {
                $inline[] = $this->resolve($prelude . ' {' . $body . '}', $tokens);
                continue;
            }

            $head[] = $this->atRule($prelude, $body, $tokens);
        }

        $this->inline = implode("\n", $inline);
        $this->head = implode("\n", array_filter($head, static fn(string $rule): bool => $rule !== ''));
    }

    /**
     * At-rule with its tokens resolved, and without the ":root" of its block
     *
     * @param string                $prelude Prelude, such as "@media (prefers-color-scheme: dark)"
     * @param string|null           $body    Block, or NULL for a statement such as "@import"
     * @param array<string, string> $tokens  Tokens of the enclosing level
     *
     * @return string Rule, or an empty string if its block is empty
     */
    private function atRule(string $prelude, ?string $body, array $tokens): string
    {
        if ($body === null) {
            return str_starts_with(strtolower($prelude), '@charset') ? '' : $this->resolve($prelude . ';', $tokens);
        }

        $statements = $this->statements($body);
        $tokens = [...$tokens, ...$this->tokens($statements)];
        $rules = [];
        foreach ($statements as [$innerPrelude, $innerBody]) {
            if ($this->isRoot($innerPrelude)) {
                continue;
            }

            $rules[] = ($innerBody === null)
                ? $this->resolve($innerPrelude . ';', $tokens)
                : (str_starts_with($innerPrelude, '@')
                    ? $this->atRule($innerPrelude, $innerBody, $tokens)
                    : $this->resolve($innerPrelude . ' {' . $innerBody . '}', $tokens));
        }

        $rules = array_filter($rules, static fn(string $rule): bool => $rule !== '');

        // A block of declarations, such as of "@font-face", stays as it is
        if (($rules === []) && ($statements === []) && (trim($body) !== '')) {
            return $this->resolve($prelude . ' {' . $body . '}', $tokens);
        }

        return ($rules === []) ? '' : $prelude . " {\n" . implode("\n", $rules) . "\n}";
    }

    /**
     * Statements of one level of CSS
     *
     * @param string $css CSS without comments
     *
     * @return list<array{string, string|null}> Prelude and block of each statement, with NULL for no block
     */
    private function statements(string $css): array
    {
        $statements = [];
        $length = \strlen($css);
        $start = 0;
        $open = null;
        $depth = 0;
        for ($position = 0; $position < $length; ++$position) {
            $skipped = $this->skip($css, $position);
            if ($skipped > $position) {
                $position = $skipped;
                continue;
            }

            $character = $css[$position];
            if ($character === '{') {
                $open ??= $position;
                ++$depth;
            } elseif (($character === '}') && ($depth > 0) && (--$depth === 0)) {
                $statements[] = [
                    trim(substr($css, $start, (int) $open - $start)),
                    substr($css, (int) $open + 1, $position - (int) $open - 1),
                ];
                $start = $position + 1;
                $open = null;
            } elseif (($character === ';') && ($depth === 0)) {
                $statements[] = [trim(substr($css, $start, $position - $start)), null];
                $start = $position + 1;
            }
        }

        // Without a block, only at-rules such as "@import" are statements
        return array_values(array_filter(
            $statements,
            static fn(array $statement): bool => ($statement[1] !== null) || str_starts_with($statement[0], '@'),
        ));
    }

    /**
     * Last position of a string or an escape that starts at a position
     *
     * @param string  $css      CSS
     * @param integer $position Position
     *
     * @return integer Position of the closing quote or the escaped character, or the position itself
     */
    private function skip(string $css, int $position): int
    {
        return match ($css[$position]) {
            '"', "'" => $this->stringEnd($css, $position),
            '\\' => $position + 1,
            default => $position,
        };
    }

    /**
     * Position of the quote that ends a string
     *
     * @param string  $css   CSS
     * @param integer $start Position of the quote that starts the string
     *
     * @return integer Position of the closing quote, or of the last character if the string does not end
     */
    private function stringEnd(string $css, int $start): int
    {
        $quote = $css[$start];
        $length = \strlen($css);
        for ($position = $start + 1; $position < $length; ++$position) {
            if ($css[$position] === '\\') {
                ++$position;
            } elseif ($css[$position] === $quote) {
                return $position;
            }
        }

        return $length - 1;
    }

    /**
     * Tokens that the ":root" rules of a level declare
     *
     * @param list<array{string, string|null}> $statements Statements of the level
     *
     * @return array<string, string> Values by name without the dashes
     */
    private function tokens(array $statements): array
    {
        $tokens = [];
        foreach ($statements as [$prelude, $body]) {
            if (!$this->isRoot($prelude)) {
                continue;
            }

            // The last declaration needs no semicolon
            $pattern = '/--([a-z0-9-]+)\s*:\s*([^;]+?)\s*(?:;|$)/i';
            preg_match_all($pattern, trim((string) $body), $declared, PREG_SET_ORDER);
            foreach ($declared as [, $name, $value]) {
                $tokens[$name] = $value;
            }
        }

        return $tokens;
    }

    /**
     * Whether a prelude selects only the root
     *
     * @param string $prelude Prelude
     *
     * @return boolean
     */
    private function isRoot(string $prelude): bool
    {
        return strtolower($prelude) === ':root';
    }

    /**
     * Replace the tokens of CSS with their values
     *
     * @param string                $css    CSS
     * @param array<string, string> $tokens Tokens
     *
     * @return string CSS, with a token that has neither a value nor a fallback left as it is
     */
    private function resolve(string $css, array $tokens): string
    {
        for ($depth = 0; $depth < self::DEPTH && str_contains($css, 'var('); ++$depth) {
            $css = (string) preg_replace_callback(
                '/var\(\s*--([a-z0-9-]+)\s*(?:,\s*([^()]*))?\)/i',
                static function (array $match) use ($tokens): string {
                    $fallback = trim($match[2] ?? '');

                    return $tokens[$match[1]] ?? (($fallback === '') ? $match[0] : $fallback);
                },
                $css,
            );
        }

        return $css;
    }
}
