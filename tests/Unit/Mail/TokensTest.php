<?php

/**
 * The tokens of the mail components have the same defaults in the stylesheet and in the settings
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
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Tollwerk\TwBase\Mail\StyleInliner;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * The tokens of the mail components have the same defaults in the stylesheet and in the settings
 *
 * The stylesheet serves the components in the browser, such as in a component library; the settings serve mails.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class TokensTest extends UnitTestCase
{
    /**
     * Each token in ":root" of the frame has a setting with the same default, and each setting has a token
     *
     * @return void
     *
     * @throws ParseException If the definitions of the settings are invalid
     */
    #[Test]
    public function stylesheetAndSettingsAgree(): void
    {
        $root = \dirname(__DIR__, 3);
        $css = (string) file_get_contents($root . '/Resources/Public/Components/Mail/Frame/Frame.css');
        preg_match('/:root\s*\{([^}]*)\}/', $css, $block);
        preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/', $block[1] ?? '', $declared, PREG_SET_ORDER);
        $stylesheet = [];
        foreach ($declared as [, $name, $value]) {
            $stylesheet[$name] = trim($value);
        }

        $definitions = Yaml::parseFile($root . '/Configuration/Sets/TwBase/settings.definitions.yaml');
        self::assertIsArray($definitions);
        self::assertIsArray($definitions['settings'] ?? null);
        $settings = [];
        foreach ($definitions['settings'] as $key => $definition) {
            $property = StyleInliner::property((string) $key);
            if (null === $property || !\is_array($definition)) {
                continue;
            }

            $settings[$property] = $definition['default'] ?? null;
        }

        ksort($stylesheet);
        ksort($settings);
        self::assertNotSame([], $stylesheet);
        self::assertSame($stylesheet, $settings);
    }
}
