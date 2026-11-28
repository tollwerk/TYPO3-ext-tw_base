<?php

/**
 * Answer a request for an example with its HTML, once the frontend prepared the page
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

use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tollwerk\TwBase\Component\Example\ExampleEndpoint;
use Tollwerk\TwBase\Component\Example\ExampleRenderer;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Frontend\Resource\PublicUrlPrefixer;
use UnexpectedValueException;

/**
 * Answer a request for an example with its HTML, once the frontend prepared the page
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final readonly class ExampleResponse implements MiddlewareInterface
{
    /**
     * Constructor
     *
     * @param ExampleRenderer  $renderer  Renderer of examples
     * @param ListenerProvider $listeners Listeners of events, for the prefix of the addresses of files
     */
    public function __construct(private ExampleRenderer $renderer, private ListenerProvider $listeners) {}

    /**
     * Render the example the request asks for, instead of the page
     *
     * @param ServerRequestInterface  $request Request
     * @param RequestHandlerInterface $handler Next handler
     *
     * @return ResponseInterface The HTML of the example; status 404 with the reason if it cannot render
     *
     * @throws InvalidArgumentException If the response cannot be built
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requested = $request->getAttribute(ExampleEndpoint::ATTRIBUTE);
        if (!\is_array($requested) || !\is_string($requested[0] ?? null) || !\is_string($requested[1] ?? null)) {
            return $handler->handle($request);
        }

        // As the RequestHandler of the frontend does before it renders a page, which the example replaces
        $GLOBALS['TYPO3_REQUEST'] = $request;
        $this->listeners->addListener(
            GeneratePublicUrlForResourceEvent::class,
            PublicUrlPrefixer::class,
            'prefixWithAbsRefPrefix',
        );

        try {
            return new HtmlResponse($this->renderer->render($requested[0], $requested[1], $request));
        } catch (UnexpectedValueException $exception) {
            return new HtmlResponse(htmlspecialchars($exception->getMessage()), 404);
        }
    }
}
