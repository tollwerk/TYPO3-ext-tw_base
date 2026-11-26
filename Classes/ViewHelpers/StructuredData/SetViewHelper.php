<?php

/**
 * Sets a property of a node of the JSON-LD graph
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
 * Sets a property of a node of the JSON-LD graph
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class SetViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('id', 'string', 'Id of the node', true);
        $this->registerArgument('key', 'mixed', 'Key, with dots between nested properties, or its parts', true);
        $this->registerArgument('value', 'mixed', 'Value', true);
    }

    /**
     * Set the value
     *
     * @return string Nothing
     */
    public function render(): string
    {
        $key = $this->arguments['key'] ?? '';
        $this->graph()->set(
            $this->stringArgument('id') ?? '',
            \is_array($key) ? array_values(array_filter($key, \is_string(...))) : $this->stringArgument('key') ?? '',
            $this->arguments['value'] ?? null,
        );

        return '';
    }
}
