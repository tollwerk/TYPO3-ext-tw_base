<?php

/**
 * Find what keeps a mail from being accessible
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

use Dom\Element;
use Dom\HTMLDocument;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Mail\FluidEmail;

/**
 * Find what keeps a mail from being accessible
 *
 * The tests of each extension check their registered mails with it: `self::assertSame([], $inspector->inspect($mail))`.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final readonly class MailInspector
{
    /**
     * Texts of links that say nothing about where they lead
     *
     * @var list<string>
     */
    private const array VAGUE = [
        'click here',
        'here',
        'link',
        'more',
        'read more',
        'hier',
        'klicken sie hier',
        'mehr',
        'weiter',
    ];

    /**
     * Problems of a composed mail
     *
     * @param FluidEmail $mail Mail
     *
     * @return list<string> Problems, empty if there are none
     */
    public function inspect(FluidEmail $mail): array
    {
        return $this->problems((string) $mail->getHtmlBody(), (string) $mail->getTextBody());
    }

    /**
     * Problems of the parts of a mail
     *
     * @param string $html HTML part
     * @param string $text Text part
     *
     * @return list<string> Problems, empty if there are none
     */
    public function problems(string $html, string $text): array
    {
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $problems = $this->documentProblems($document);
        foreach ($document->querySelectorAll('a') as $link) {
            $problems = [...$problems, ...$this->linkProblems($link, $text)];
        }

        foreach ($document->querySelectorAll('.MailCode') as $code) {
            $value = trim($code->textContent ?? '');
            if (str_contains($text, $value)) {
                continue;
            }

            $problems[] = \sprintf('The text part lacks the code "%s"', $value);
        }

        if (str_contains($html, 'var(--')) {
            $problems[] = 'The HTML has tokens without a value';
        }

        foreach ($document->querySelectorAll('body *') as $element) {
            $problems[] = $this->contrastProblem($element);
        }

        if ('' === trim($text)) {
            $problems[] = 'The text part is empty';
        } elseif (1 === preg_match('/<[a-z!\/]/i', $text)) {
            $problems[] = 'The text part has markup';
        }

        return array_values(array_filter($problems, \is_string(...)));
    }

    /**
     * Problems of the document as a whole: language, title, color scheme, headings, tables and images
     *
     * @param HTMLDocument $document Document
     *
     * @return list<string>
     */
    private function documentProblems(HTMLDocument $document): array
    {
        $problems = [];
        if ('' === trim($document->documentElement?->getAttribute('lang') ?? '')) {
            $problems[] = 'The HTML has no language';
        }

        if ('' === trim($document->title)) {
            $problems[] = 'The HTML has no title';
        }

        if (!$document->querySelector('meta[name="color-scheme"]') instanceof Element) {
            $problems[] = 'The HTML declares no color scheme';
        }

        $headings = $document->querySelectorAll('h1')->length;
        if (1 !== $headings) {
            $problems[] = \sprintf('The HTML has %d headings of the first level instead of one', $headings);
        }

        if ($document->querySelectorAll('table')->length > 0) {
            $problems[] = 'The HTML has tables, which mails do not need for layout';
        }

        foreach ($document->querySelectorAll('img:not([alt])') as $image) {
            $problems[] = \sprintf('The image "%s" has no alternative text', $this->attribute($image, 'src'));
        }

        return $problems;
    }

    /**
     * Problems of a link: no text, text that says nothing, an address the text part lacks
     *
     * @param Element $link Link
     * @param string  $text Text part
     *
     * @return list<string>
     */
    private function linkProblems(Element $link, string $text): array
    {
        $href = $this->attribute($link, 'href');
        $label = trim((string) preg_replace('/\s+/', ' ', $link->textContent ?? ''));
        $problems = [];
        if ('' === $label && '' === $this->attribute($link, 'aria-label')) {
            $problems[] = \sprintf('The link to "%s" has no text', $href);
        } elseif (\in_array(mb_strtolower($label), self::VAGUE, true)) {
            $problems[] = \sprintf(
                'The text "%s" of the link to "%s" says nothing about where it leads',
                $label,
                $href,
            );
        }

        $address = str_starts_with($href, 'mailto:') ? substr($href, 7) : $href;
        if ('' !== $address && !str_contains($text, $address)) {
            $problems[] = \sprintf('The text part lacks "%s"', $address);
        }

        return $problems;
    }

    /**
     * Problem with the contrast of the text of an element, from the colors in the style attributes
     *
     * Text needs 4.5:1, large text of at least 24px, or 18.66px in bold, needs 3:1.
     *
     * @param Element $element Element
     *
     * @return string|null The problem, or null if there is none or the element has no colors
     */
    private function contrastProblem(Element $element): ?string
    {
        $text = '';
        foreach ($element->childNodes as $child) {
            if (XML_TEXT_NODE !== $child->nodeType) {
                continue;
            }

            $text .= $child->textContent;
        }

        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        $colorValue = $this->style($element, '/(?:^|;)\s*color\s*:\s*([^;]+)/i');
        $backgroundValue = $this->style($element, '/(?:^|;)\s*background(?:-color)?\s*:\s*([^;]+)/i');
        // A token without a value is a problem of its own
        $values = $colorValue . $backgroundValue;
        if (in_array('', [$text, $colorValue, $backgroundValue], true) || str_contains($values, 'var(')) {
            return null;
        }

        $color = $this->luminance($colorValue);
        $background = $this->luminance($backgroundValue);
        if (null === $color || null === $background) {
            return \sprintf(
                'The contrast of the text "%s" cannot be checked, as "%s" is no opaque color in hex or rgb() notation',
                mb_substr($text, 0, 40),
                (null === $color) ? $colorValue : $backgroundValue,
            );
        }

        $ratio = (max($color, $background) + 0.05) / (min($color, $background) + 0.05);
        $size = (float) $this->style($element, '/(?:^|;)\s*font-size\s*:\s*([\d.]+)px/i');
        $bold = 1 === preg_match('/bold|[6-9]00/', $this->style($element, '/(?:^|;)\s*font-weight\s*:\s*([^;]+)/i'));
        $minimum = $size >= 24 || ($bold && $size >= 18.66) ? 3.0 : 4.5;

        if ($ratio + 0.005 >= $minimum) {
            return null;
        }

        return \sprintf(
            'The text "%s" has a contrast of %.2f:1 instead of %.1f:1',
            mb_substr($text, 0, 40),
            $ratio,
            $minimum,
        );
    }

    /**
     * Value of a property in the style attribute of an element or of the nearest ancestor that has it
     *
     * @param Element $element Element
     * @param string  $pattern Pattern with the value as first group
     *
     * @return string Value, or an empty string
     */
    private function style(Element $element, string $pattern): string
    {
        for ($current = $element; $current instanceof Element; $current = $current->parentElement) {
            if (preg_match_all($pattern, $this->attribute($current, 'style'), $matches) > 0) {
                $values = $matches[1] ?? [];

                // The value keeps "!important", which luminance() ignores; trim() with it would also take an "a"
                return trim((string) end($values));
            }
        }

        return '';
    }

    /**
     * Relative luminance of an opaque color
     *
     * @param string $color Color in hexadecimal or rgb() notation, such as "#0b4f8a", "#fff" or "rgb(11 79 138)"
     *
     * @return float|null Null if the color has another notation or is transparent
     */
    private function luminance(string $color): ?float
    {
        $channels = $this->channels(strtolower(trim(str_replace('!important', '', $color))));
        if (null === $channels) {
            return null;
        }

        $linear = array_map(static function (float $channel): float {
            $value = $channel / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $channels);

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    /**
     * Red, green and blue of an opaque color
     *
     * @param string $color Color in lower case
     *
     * @return array{float, float, float}|null Channels from 0 to 255, or null if the color has another notation or is
     *                                         transparent
     */
    private function channels(string $color): ?array
    {
        if (1 === preg_match('/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $color, $hex)) {
            $digits = (\strlen($hex[1]) <= 4) ? (string) preg_replace('/./', '$0$0', $hex[1]) : $hex[1];
            $values = array_map(static fn(string $pair): float => (float) hexdec($pair), str_split($digits, 2));

            return (($values[3] ?? 255.0) < 255.0) ? null : [$values[0] ?? 0.0, $values[1] ?? 0.0, $values[2] ?? 0.0];
        }

        $number = '([\d.]+%?)';
        $pattern = '#^rgba?\(\s*' . $number . '[\s,]+' . $number . '[\s,]+' . $number
            . '(?:\s*[,/]\s*' . $number . ')?\s*\)$#';
        if (1 !== preg_match($pattern, $color, $rgb)) {
            return null;
        }

        $alpha = $rgb[4] ?? '1';
        $alpha = str_ends_with($alpha, '%') ? (float) $alpha / 100 : (float) $alpha;
        if ($alpha < 1.0) {
            return null;
        }

        $channel = static fn(string $value): float => min(
            255.0,
            str_ends_with($value, '%') ? (float) $value * 2.55 : (float) $value,
        );

        return [$channel($rgb[1]), $channel($rgb[2]), $channel($rgb[3])];
    }

    /**
     * Attribute of an element, or an empty string
     *
     * @param Element $element Element
     * @param string  $name    Name of the attribute
     *
     * @return string
     */
    private function attribute(Element $element, string $name): string
    {
        return trim($element->getAttribute($name) ?? '');
    }
}
