<?php

/**
 * Renders its content with a node as the main entity
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\StructuredData\EntityContext;

use Override;
use Tollwerk\TwBase\ViewHelpers\StructuredData\AbstractStructuredDataViewHelper;

/**
 * Renders its content with a node as the main entity
 *
 * Parts inside, such as content elements, add their properties to the main entity without knowing it.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class WrapViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * The content is already rendered markup
     *
     * @var boolean
     */
    #[Override]
    protected $escapeOutput = false;

    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('id', 'string', 'Id of the main entity', true);
    }

    /**
     * Render the content
     *
     * @return string
     */
    public function render(): string
    {
        $graph = $this->graph();
        $graph->pushMainEntity($this->stringArgument('id') ?? '');
        $content = $this->renderedChildren();
        $graph->popMainEntity();

        return $content;
    }
}
