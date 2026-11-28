<?php

/**
 * Heading levels while a page renders
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Tests\Unit\Heading;

use Psr\Log\NullLogger;
use Tollwerk\TwBase\Heading\HeadingContextManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Heading levels while a page renders
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class HeadingContextManagerTest extends UnitTestCase
{
    /**
     * Headings without level get the next one, and the level returns when a section ends
     *
     * @return void
     */
    public function testLevelsFollowTheNesting(): void
    {
        $manager = new HeadingContextManager(new NullLogger());
        $page = $manager->setUp();
        $section = $manager->setUp();

        self::assertSame(1, $page->level);
        self::assertSame('xx-large', $page->visualType);
        self::assertSame(2, $section->level);
        self::assertSame(3, $manager->nextLevel());
        self::assertSame(5, $manager->nextLevel(5));

        $manager->tearDown($section);
        self::assertSame(1, $manager->currentLevel());
        self::assertSame(2, $manager->setUp()->level);
    }

    /**
     * Skipped levels are recorded, visual types can differ, and high levels hide the heading
     *
     * @return void
     */
    public function testSkippedTypedAndHiddenHeadings(): void
    {
        $manager = new HeadingContextManager(new NullLogger());
        $skipping = $manager->setUp(3, 1);

        self::assertSame([1, 2], $skipping->skipped);
        self::assertSame('xx-large', $skipping->visualType);
        self::assertSame(1, $manager->currentType());

        $hidden = $manager->setUp(HeadingContextManager::HIDDEN_LEVELS);
        self::assertTrue($hidden->hidden);
        self::assertSame(4, $hidden->level);
        self::assertSame('medium', $hidden->visualType);
        self::assertSame('x-small', $manager->setUp(9)->visualType);
        self::assertSame('x-large', $manager->setUp(2, 0)->visualType);
        self::assertSame(2, $manager->currentType());
    }

    /**
     * After restoring a marker, the type is that of the heading of the restored context
     *
     * @return void
     */
    public function testRestoringATypeWithItsContext(): void
    {
        $manager = new HeadingContextManager(new NullLogger());
        $manager->setUp(1);
        $marker = $manager->currentMarker();
        $manager->setUp(2, 6);
        $manager->setUp(3, 5);

        $manager->restore($marker);
        self::assertSame(1, $manager->currentType());
        $manager->restore('');
        self::assertSame(0, $manager->currentType());
    }

    /**
     * Restoring a marker closes the contexts opened after it
     *
     * @return void
     */
    public function testRestoringAMarker(): void
    {
        $manager = new HeadingContextManager(new NullLogger());
        self::assertSame('', $manager->currentMarker());

        $manager->setUp();
        $marker = $manager->currentMarker();
        $manager->setUp();
        $manager->setUp();
        self::assertSame(3, $manager->currentLevel());

        self::assertSame(1, $manager->restore($marker));
        self::assertSame($marker, $manager->currentMarker());
        self::assertSame(1, $manager->restore(''));
        self::assertSame(0, $manager->restore('', true));
        self::assertSame(0, $manager->restore(''));

        $manager->setUp(2);
        $manager->reset();
        self::assertSame(0, $manager->currentLevel());
        self::assertSame(0, $manager->currentType());
        self::assertSame('', $manager->currentMarker());
    }
}
