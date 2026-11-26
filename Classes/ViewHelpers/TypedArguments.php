<?php

/**
 * Typed access to the arguments and the content of a ViewHelper
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

use Psr\Http\Message\ServerRequestInterface;
use Stringable;

/**
 * Typed access to the arguments and the content of a ViewHelper
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
trait TypedArguments
{
    /**
     * Argument as a string
     *
     * @param string $name Name
     *
     * @return string|null String, or NULL if the argument is missing or not scalar
     */
    protected function stringArgument(string $name): ?string
    {
        $value = $this->arguments[$name] ?? null;

        return \is_scalar($value) ? (string) $value : null;
    }

    /**
     * Argument as an integer
     *
     * @param string $name Name
     *
     * @return integer|null Integer, or NULL if the argument is missing or not numeric
     */
    protected function intArgument(string $name): ?int
    {
        $value = $this->arguments[$name] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Argument as a boolean
     *
     * @param string $name Name
     *
     * @return boolean
     */
    protected function boolArgument(string $name): bool
    {
        return (bool) ($this->arguments[$name] ?? false);
    }

    /**
     * Rendered content
     *
     * @return string
     */
    protected function renderedChildren(): string
    {
        $children = $this->renderChildren();

        return (\is_scalar($children) || $children instanceof Stringable) ? (string) $children : '';
    }

    /**
     * Request that renders the template
     *
     * @return ServerRequestInterface|null Request, or NULL outside of a request
     */
    protected function request(): ?ServerRequestInterface
    {
        $context = $this->renderingContext;

        return ($context?->hasAttribute(ServerRequestInterface::class) ?? false)
            ? $context->getAttribute(ServerRequestInterface::class)
            : null;
    }
}
