<?php

/**
 * Registers a node of the JSON-LD graph
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
 * Registers a node of the JSON-LD graph
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class RegisterViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('type', 'string', 'Type, such as "Event"', true);
        $this->registerArgument('id', 'string', 'Id; ids starting with # are relative to the page', true);
        $this->registerArgument('data', 'array', 'Properties', false, []);
    }

    /**
     * Register the node
     *
     * @return string Nothing
     */
    public function render(): string
    {
        $data = $this->arguments['data'] ?? [];
        $this->graph()->register(
            $this->stringArgument('type') ?? 'Thing',
            $this->stringArgument('id') ?? '',
            \is_array($data) ? array_filter($data, \is_string(...), ARRAY_FILTER_USE_KEY) : [],
        );

        return '';
    }
}
