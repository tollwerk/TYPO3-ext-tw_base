<?php

/**
 * Write the styles of the mail components into the HTML of a mail
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

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mime\RawMessage;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WeakMap;

/**
 * Write the styles of the mail components into the HTML of a mail
 *
 * Mail components have their stylesheets like any component, with the tokens as custom properties in ":root".
 * Many mail programs know neither stylesheets nor custom properties, so the inliner renders the HTML, takes the
 * stylesheets of the components it used and the one of the setting "twBase.mail.stylesheet", replaces each token
 * with its value and writes the rules into the style attributes. Rules for media, such as the dark mode, go into the
 * head. A site changes a token with a setting below "twBase.mail.tokens": "twBase.mail.tokens.color.accentText" sets
 * "--mail-color-accent-text". The stylesheets of the mail never reach the page that sends it.
 *
 * FluidEmail renders again after every change, such as assign(), and loses the styles. The inliner therefore
 * remembers the mails it wrote the styles into and writes them again just before such a mail is sent, see
 * EventListener\InlineStyles.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final readonly class StyleInliner
{
    /**
     * Prefix of the settings with tokens
     *
     * @var string
     */
    private const string TOKENS = 'twBase.mail.tokens.';

    /**
     * Rules for media, as the inliner finds them
     *
     * @var string
     */
    private const string MEDIA = '/@media [^{]*+{([^{}]++|{[^{}]*+})*+}/';

    /**
     * Mails with styles, with their sites
     *
     * @var WeakMap<FluidEmail, Site|null>
     */
    private WeakMap $mails;

    /**
     * Constructor
     *
     * @param AssetCollector $assetCollector Asset collector, which collects the stylesheets of the components
     */
    public function __construct(private AssetCollector $assetCollector)
    {
        $this->mails = new WeakMap();
    }

    /**
     * Render the HTML of a mail and write its styles into it
     *
     * @param FluidEmail $mail Mail
     * @param Site|null  $site Site, for its tokens and stylesheet
     *
     * @return void
     */
    public function inline(FluidEmail $mail, ?Site $site): void
    {
        $settings = [];
        foreach ($site?->getSettings()->getAllFlat() ?? [] as $key => $value) {
            $settings[(string) $key] = $value;
        }

        $state = $this->assetCollector->getState();
        $empty = array_map(static fn(mixed $value): mixed => \is_array($value) ? [] : $value, $state);
        $this->assetCollector->updateState($empty);
        try {
            // Rendering anew collects the stylesheets of the components even if the mail has its HTML already
            $html = (string) $mail->getHtmlBody(true);
            $sources = [
                ...array_column($this->assetCollector->getInlineStyleSheets(), 'source'),
                ...array_map($this->file(...), array_column($this->assetCollector->getStyleSheets(), 'source')),
            ];
        } finally {
            $this->assetCollector->updateState($state);
        }

        $stylesheet = $settings['twBase.mail.stylesheet'] ?? '';
        if (\is_string($stylesheet) && '' !== $stylesheet) {
            $sources[] = $this->file($stylesheet);
        }

        $css = $this->resolve(implode("\n", array_filter($sources, \is_string(...))), $settings);
        preg_match_all(self::MEDIA, $css, $media);
        $inlined = new CssToInlineStyles()->convert($html, (string) preg_replace(self::MEDIA, '', $css));
        if ([] !== $media[0]) {
            $style = "<style>\n" . implode("\n", $media[0]) . "\n</style>\n</head>";
            $inlined = (string) preg_replace('#</head>#i', $style, $inlined, 1);
        }

        $mail->html($inlined);
        $this->mails[$mail] = $site;
    }

    /**
     * Write the styles again into a mail that had them, as it may have changed since
     *
     * @param RawMessage $message Message that is about to be sent
     *
     * @return void
     */
    public function refresh(RawMessage $message): void
    {
        if (!($message instanceof FluidEmail) || !$this->mails->offsetExists($message)) {
            return;
        }

        $this->inline($message, $this->mails[$message]);
    }

    /**
     * Custom property of a setting with a token
     *
     * @param string $setting Setting, such as "twBase.mail.tokens.dark.color.accentText"
     *
     * @return string|null Property without the dashes, such as "mail-dark-color-accent-text", or null if the setting
     *                     has no token
     */
    public static function property(string $setting): ?string
    {
        if (!str_starts_with($setting, self::TOKENS)) {
            return null;
        }

        $name = str_replace('.', '-', substr($setting, \strlen(self::TOKENS)));

        return 'mail-' . strtolower((string) preg_replace('/(?<=[a-z0-9])([A-Z])/', '-$1', $name));
    }

    /**
     * Content of a stylesheet
     *
     * @param mixed $source Path with "EXT:" or relative to the public folder
     *
     * @return string
     */
    private function file(mixed $source): string
    {
        $file = \is_string($source) ? GeneralUtility::getFileAbsFileName($source) : '';

        return '' !== $file && is_file($file) ? (string) file_get_contents($file) : '';
    }

    /**
     * Replace the tokens with their values, from ":root" or from the settings of the site
     *
     * @param string               $css      CSS
     * @param array<string, mixed> $settings Settings of the site, flat
     *
     * @return string CSS without comments, ":root" and tokens
     */
    private function resolve(string $css, array $settings): string
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $tokens = [];
        preg_match_all('/:root\s*\{([^}]*)\}/', $css, $roots);
        foreach ($roots[1] as $declarations) {
            preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $declarations, $declared, PREG_SET_ORDER);
            foreach ($declared as [, $name, $value]) {
                $tokens[$name] = trim($value);
            }
        }

        foreach ($settings as $key => $value) {
            $property = self::property($key);
            if (null === $property || !\is_string($value) || '' === trim($value)) {
                continue;
            }

            $tokens[$property] = trim($value);
        }

        $css = (string) preg_replace('/:root\s*\{[^}]*\}/', '', $css);
        for ($depth = 0; $depth < 5 && str_contains($css, 'var('); ++$depth) {
            $css = (string) preg_replace_callback(
                '/var\(\s*--([a-z0-9-]+)\s*(?:,\s*([^()]*))?\)/i',
                static function (array $match) use ($tokens): string {
                    $fallback = trim($match[2] ?? '');

                    return $tokens[$match[1]] ?? ('' === $fallback ? $match[0] : $fallback);
                },
                $css,
            );
        }

        return $css;
    }
}
