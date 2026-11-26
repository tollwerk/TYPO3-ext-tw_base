<?php

/**
 * Returns the marker of the current heading context
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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the marker of the current heading context
 *
 * A content element takes the marker before it renders and restores it afterwards, so its headings do not change the
 * levels of the next element.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class GetViewHelper extends AbstractViewHelper
{
    /**
     * Constructor
     *
     * @param HeadingContextManager $headingContextManager Heading levels
     */
    public function __construct(private readonly HeadingContextManager $headingContextManager) {}

    /**
     * Return the marker
     *
     * @return string
     */
    public function render(): string
    {
        return $this->headingContextManager->currentMarker();
    }
}
