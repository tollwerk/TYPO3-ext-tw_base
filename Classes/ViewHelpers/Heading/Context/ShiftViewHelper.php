<?php

/**
 * Renders its content with the heading levels shifted
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

use Override;
use Tollwerk\TwBase\Heading\HeadingContextManager;
use Tollwerk\TwBase\ViewHelpers\TypedArguments;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders its content with the heading levels shifted
 *
 * Useful where a part of the page has a heading the markup does not show, such as the summary of a collapsible
 * element.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class ShiftViewHelper extends AbstractViewHelper
{
    use TypedArguments;

    /**
     * The content is already rendered markup
     *
     * @var boolean
     */
    #[Override]
    protected $escapeOutput = false;

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
        $this->registerArgument('by', 'int', 'Number of levels to shift by', false, 0);
    }

    /**
     * Render the content
     *
     * @return string
     */
    public function render(): string
    {
        $by = $this->intArgument('by') ?? 0;
        if ($by <= 0) {
            return $this->renderedChildren();
        }

        $marker = $this->headingContextManager->currentMarker();
        $this->headingContextManager->setUp($this->headingContextManager->currentLevel() + $by);
        $content = $this->renderedChildren();
        $this->headingContextManager->restore($marker, true);

        return $content;
    }
}
