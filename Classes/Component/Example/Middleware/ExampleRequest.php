<?php

/**
 * Recognise a signed request for an example, before the frontend prepares the page
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\Example\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tollwerk\TwBase\Component\Example\ExampleEndpoint;
use TYPO3\CMS\Frontend\Cache\CacheInstruction;

/**
 * Recognise a signed request for an example, before the frontend prepares the page
 *
 * The request gets the component and the example as attribute, which only this middleware sets, and does without
 * the page cache: a page from the cache has only the configuration of its TypoScript, and examples need all of it.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class ExampleRequest implements MiddlewareInterface
{
    /**
     * Constructor
     *
     * @param ExampleEndpoint $endpoint Addresses of examples
     */
    public function __construct(private ExampleEndpoint $endpoint) {}

    /**
     * Mark a signed request for an example
     *
     * @param ServerRequestInterface  $request Request
     * @param RequestHandlerInterface $handler Next handler
     *
     * @return ResponseInterface
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requested = $this->endpoint->requested($request);
        if (null === $requested) {
            return $handler->handle($request->withoutAttribute(ExampleEndpoint::ATTRIBUTE));
        }

        $instruction = $request->getAttribute('frontend.cache.instruction');
        $instruction = $instruction instanceof CacheInstruction ? $instruction : new CacheInstruction();
        $instruction->disableCache('EXT:tw_base: An example of a component renders without the page cache.');

        return $handler->handle(
            $request
                ->withAttribute('frontend.cache.instruction', $instruction)
                ->withAttribute(ExampleEndpoint::ATTRIBUTE, $requested),
        );
    }
}
