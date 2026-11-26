<?php

/**
 * Restores the heading levels to an earlier context
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\Heading\Context;

use Tollwerk\TwBase\Heading\HeadingContextManager;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Restores the heading levels to an earlier context
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class RestoreViewHelper extends AbstractViewHelper
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
        $this->registerArgument('context', 'string', 'Marker of the context', true);
        $this->registerArgument('root', 'bool', 'Whether to start over at the root without a marker', false, false);
    }

    /**
     * Restore the levels
     *
     * @return string Nothing
     */
    public function render(): string
    {
        $this->headingContextManager->restore($this->stringArgument('context') ?? '', $this->boolArgument('root'));

        return '';
    }
}
