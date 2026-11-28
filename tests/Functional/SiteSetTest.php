<?php

/**
 * Tests of the site set of tw_base
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

use Override;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerExceptionInterface;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Set\SetRegistry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests of the site set of tw_base
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class SiteSetTest extends FunctionalTestCase
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
     * The set, its categories and its settings have labels from labels.xlf, in every language of the extension
     *
     * @return void
     *
     * @throws ContainerExceptionInterface If a service is missing
     */
    #[Test]
    public function labelsAreTranslations(): void
    {
        $set = $this->get(SetRegistry::class)->getSet('tollwerk/tw-base');
        self::assertNotNull($set);
        $german = $this->get(LanguageServiceFactory::class)->create('de');
        $english = $this->get(LanguageServiceFactory::class)->create('en');

        self::assertSame('Grundlagen', $german->sL($set->label));
        $categories = [];
        foreach ($set->categoryDefinitions as $category) {
            $categories[$category->key] = $category;
        }

        $settings = [];
        foreach ($set->settingsDefinitions as $setting) {
            $settings[$setting->key] = $setting;
            self::assertNotSame('', $english->sL($setting->label), $setting->key);
        }

        self::assertSame('Gestaltung', $german->sL($categories['twBase.mail.tokens']->label ?? ''));
        self::assertSame('Absender', $german->sL($settings['twBase.mail.sender']->label ?? ''));
        $description = $german->sL($settings['twBase.mail.sender']->description ?? '');
        self::assertStringStartsWith('Wer die E-Mails sendet', $description);
        $dark = $settings['twBase.mail.tokens.dark.color.text']->label ?? '';
        self::assertSame('Text im dunklen Modus', $german->sL($dark));
    }
}
