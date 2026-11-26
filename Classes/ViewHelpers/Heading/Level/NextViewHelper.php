<?php

/**
 * Returns the level the next heading gets
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\Heading\Level;

use Tollwerk\TwBase\Heading\HeadingContextManager;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the level the next heading gets
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class NextViewHelper extends AbstractViewHelper
{
    use TypedArguments;

    /**
     * Constructor
     *
     * @param HeadingContextManager $headingContextManager Heading levels
     */
    public function __construct(private readonly HeadingContextManager $headingContextManager) {}

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('level', 'int', 'Level, if the position does not decide it', false);
    }

    /**
     * Return the level
     *
     * @return integer
     */
    public function render(): int
    {
        return $this->headingContextManager->nextLevel($this->intArgument('level'));
    }
}
