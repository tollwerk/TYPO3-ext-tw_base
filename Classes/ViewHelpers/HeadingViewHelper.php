<?php

/**
 * Renders a heading at the level its position calls for
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers;

use Override;
use Tollwerk\TwBase\Heading\HeadingContextManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Renders a heading at the level its position calls for
 *
 * Inside the tag goes the section the heading opens; its headings get the next level. Levels beyond 6 become a div
 * with the role heading. The classes name the visual type, a skipped level and a hidden heading.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class HeadingViewHelper extends AbstractTagBasedViewHelper
{
    use TypedArguments;

    /**
     * The heading may contain markup, such as code or parts in another language; templates escape plain text
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
    public function __construct(private readonly HeadingContextManager $headingContextManager)
    {
        parent::__construct();
    }

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('content', 'string', 'Content of the heading, which may contain markup', true);
        $this->registerArgument('level', 'int', 'Level, if the position does not decide it', false);
        $this->registerArgument('type', 'int', 'Visual type as a level; omitted or 0 for the type of the level', false);
        $this->registerArgument('hidden', 'bool', 'Whether the heading is only for assistive technology', false, false);
        $this->registerArgument('restoreContext', 'bool', 'Whether the section ends with this ViewHelper', false, true);
    }

    /**
     * Render the heading, followed by its section
     *
     * @return string
     */
    public function render(): string
    {
        $context = $this->headingContextManager->setUp(
            $this->intArgument('level'),
            $this->intArgument('type'),
            $this->boolArgument('hidden'),
        );

        if ($context->level > 6) {
            $this->tag->setTagName('div');
            $this->tag->addAttribute('role', 'heading');
            $this->tag->addAttribute('aria-level', (string) $context->level);
        } else {
            $this->tag->setTagName('h' . $context->level);
        }

        $class = $this->tag->getAttribute('class');
        $this->tag->addAttribute('class', implode(' ', array_filter([
            'Heading Heading--' . $context->visualType,
            ($context->skipped === []) ? '' : 'Heading--skipped',
            $context->hidden ? 'Heading--hidden' : '',
            \is_string($class) ? trim($class) : '',
        ], static fn(string $name): bool => $name !== '')));
        $this->tag->setContent(trim($this->stringArgument('content') ?? ''));
        // In production, TYPO3 shows the error of a content element and renders on, so the section ends either way
        try {
            return $this->tag->render() . $this->renderedChildren();
        } finally {
            if ($this->boolArgument('restoreContext')) {
                $this->headingContextManager->tearDown($context);
            }
        }
    }
}
