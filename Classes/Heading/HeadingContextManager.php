<?php

/**
 * Keeps track of the heading levels while a page renders
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Heading;

use Psr\Log\LoggerInterface;

/**
 * Keeps track of the heading levels while a page renders
 *
 * A heading opens a section; the headings inside it get the next level. When the section ends, the level returns to
 * what it was before. Templates leave out the level, so the outline of a page follows from how its parts nest.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class HeadingContextManager
{
    /**
     * Visual types by level
     *
     * @var array<integer, string>
     */
    public const array VISUAL_TYPES = [
        1 => 'xx-large',
        2 => 'x-large',
        3 => 'large',
        4 => 'medium',
        5 => 'small',
        6 => 'x-small',
    ];

    /**
     * Levels from this number on stand for a hidden heading at the next level, as in earlier major versions
     *
     * @var integer
     */
    public const int HIDDEN_LEVELS = 100;

    /**
     * Current heading level, 0 before the first heading
     *
     * @var integer
     */
    private int $currentLevel = 0;

    /**
     * Visual type of the current heading as a level
     *
     * @var integer
     */
    private int $currentType = 0;

    /**
     * Open heading contexts, the innermost last
     *
     * @var list<HeadingContext>
     */
    private array $contexts = [];

    /**
     * Number of contexts created so far, for their markers
     *
     * @var integer
     */
    private int $sequence = 0;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger Logger
     */
    public function __construct(private readonly LoggerInterface $logger) {}

    /**
     * Set up the context of a heading
     *
     * @param integer|null $level      Level, or NULL for the next level
     * @param integer|null $visualType Visual type as a level, or NULL or 0 for the type of the level
     * @param boolean      $hidden     Whether the heading is only for assistive technology
     *
     * @return HeadingContext
     */
    public function setUp(?int $level = null, ?int $visualType = null, bool $hidden = false): HeadingContext
    {
        $level ??= 0;
        $hidden = $hidden || ($level >= self::HIDDEN_LEVELS);
        $level = ($level > 0) && ($level < self::HIDDEN_LEVELS) ? $level : max(1, $this->currentLevel + 1);
        $afterLevel = min(max(1, $this->currentLevel), $level);
        $skipped = ($level - $this->currentLevel > 1) ? range($this->currentLevel + 1, $level - 1) : [];
        if ($skipped !== []) {
            $this->logger->warning('A heading skips the levels {levels}', ['levels' => implode(', ', $skipped)]);
        }

        $this->currentLevel = $level;
        // 0 means the type of the level, as in earlier major versions; Fluid also turns an empty integer into 0
        $this->currentType = (($visualType ?? 0) > 0) ? $visualType : $level;
        $context = new HeadingContext(
            'heading-' . ++$this->sequence,
            $level,
            self::VISUAL_TYPES[$this->currentType] ?? self::VISUAL_TYPES[6],
            $afterLevel,
            $hidden,
            $skipped,
            $this->currentType,
        );
        $this->contexts[] = $context;

        return $context;
    }

    /**
     * Leave the section of a heading
     *
     * @param HeadingContext $context Context of the heading
     *
     * @return void
     */
    public function tearDown(HeadingContext $context): void
    {
        $this->currentLevel = $context->afterLevel;
    }

    /**
     * Marker of the current context, to restore it later
     *
     * @return string Marker, or an empty string before the first heading
     */
    public function currentMarker(): string
    {
        $context = array_last($this->contexts);

        return $context->marker ?? '';
    }

    /**
     * Current heading level
     *
     * @return integer
     */
    public function currentLevel(): int
    {
        return $this->currentLevel;
    }

    /**
     * Visual type of the heading of the current context as a level
     *
     * @return integer Type, or 0 before the first heading
     */
    public function currentType(): int
    {
        return $this->currentType;
    }

    /**
     * Level a heading gets
     *
     * @param integer|null $level Level, or NULL for the next level
     *
     * @return integer
     */
    public function nextLevel(?int $level = null): int
    {
        return (($level > 0) && ($level < self::HIDDEN_LEVELS)) ? $level : $this->currentLevel + 1;
    }

    /**
     * Restore the levels to a context
     *
     * Without a marker, the levels start over: at 0 for the root or before the first heading, at 1 otherwise.
     *
     * @param string  $marker Marker of the context
     * @param boolean $root   Whether to start over at the root without a marker
     *
     * @return integer Current level
     */
    public function restore(string $marker, bool $root = false): int
    {
        $marker = trim($marker);
        if ($marker === '') {
            $this->currentLevel = ($root || ($this->currentLevel === 0)) ? 0 : 1;
            $this->currentType = 0;
            $this->contexts = [];

            return $this->currentLevel;
        }

        while ($this->contexts !== [] && (array_last($this->contexts)?->marker !== $marker)) {
            $context = array_pop($this->contexts);
            $this->currentLevel = $context->afterLevel;
        }

        $this->currentType = array_last($this->contexts)->type ?? 0;

        return $this->currentLevel;
    }

    /**
     * Start over for the next page
     *
     * @return void
     */
    public function reset(): void
    {
        $this->currentLevel = 0;
        $this->currentType = 0;
        $this->contexts = [];
    }
}
