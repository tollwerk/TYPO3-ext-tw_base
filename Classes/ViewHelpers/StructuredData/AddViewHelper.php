<?php

/**
 * Adds a value to a property of a node of the JSON-LD graph, which becomes a list
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\ViewHelpers\StructuredData;

/**
 * Adds a value to a property of a node of the JSON-LD graph, which becomes a list
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class AddViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('id', 'string', 'Id of the node', true);
        $this->registerArgument('key', 'string', 'Key, with dots between nested properties', true);
        $this->registerArgument('value', 'mixed', 'Value', true);
    }

    /**
     * Add the value
     *
     * @return string Nothing
     */
    public function render(): string
    {
        $this->graph()->add(
            $this->stringArgument('id') ?? '',
            $this->stringArgument('key') ?? '',
            $this->arguments['value'] ?? null,
        );

        return '';
    }
}
