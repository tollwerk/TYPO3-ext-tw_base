<?php

/**
 * Addresses that render a component with one of its examples
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */

declare(strict_types=1);

namespace Tollwerk\TwBase\Component\Example;

use Doctrine\DBAL\Exception as DBALException;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tollwerk\TwBase\Routing\PageUri;
use TYPO3\CMS\Core\Crypto\HashAlgo;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Site\Entity\Site;
use UnexpectedValueException;

/**
 * Addresses that render a component with one of its examples
 *
 * The address is that of the page of the example, in its language, with the component, the example and a signature
 * in the query. The frontend prepares the page and its TypoScript as for any request, so that view helpers for links,
 * translations and texts from the rich text editor work as on the website; then it answers with the HTML of the
 * example instead of the page. Without the signature, which needs the encryption key of the installation, the address
 * is that of the page, so that nobody outside can render examples. The component library and the tests of the
 * packages render examples this way.
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
#[Autoconfigure(public: true)]
final readonly class ExampleEndpoint
{
    /**
     * Query parameter with the component and the example, such as "twc:event.teaser#cancelled"
     *
     * @var string
     */
    public const string PARAMETER = 'tw_example';

    /**
     * Query parameter with the signature
     *
     * @var string
     */
    public const string SIGNATURE = 'tw_example_signature';

    /**
     * Attribute of the request with the component and the example, once the signature is valid
     *
     * @var string
     */
    public const string ATTRIBUTE = 'tw_base.example';

    /**
     * Constructor
     *
     * @param ExampleRenderer $renderer Renderer of examples
     * @param HashService     $hashes   Service for the signature
     * @param PageUri         $pageUri  Address of the page of an example, if the site shows it
     */
    public function __construct(
        private ExampleRenderer $renderer,
        private HashService $hashes,
        private PageUri $pageUri,
    ) {}

    /**
     * The address that renders a component with one of its examples
     *
     * @param Site   $site      Site
     * @param string $component Component with its prefix, such as "twc:event.teaser"
     * @param string $example   Name of the example
     *
     * @return UriInterface
     *
     * @throws UnexpectedValueException If there is no such component or example, or the site lacks its page or language
     * @throws DBALException If the page of the example cannot be read
     * @throws InvalidArgumentException If the address cannot be built
     */
    public function uri(Site $site, string $component, string $example): UriInterface
    {
        $examples = $this->renderer->examples($component)->examples;
        $configuration = $examples[$example] ?? throw new UnexpectedValueException(
            \sprintf('The component "%s" has no example "%s"', $component, $example),
            1795776401,
        );
        $key = $component . '#' . $example;
        $page = $configuration->page ?? $site->getRootPageId();

        $error = \sprintf('The site "%s" cannot render the example "%s"', $site->getIdentifier(), $key);
        try {
            $language = null === $configuration->language
                ? $site->getDefaultLanguage()
                : $site->getLanguageById($configuration->language);
        } catch (InvalidArgumentException $exception) {
            throw new UnexpectedValueException($error, 1795776402, $exception);
        }

        // The router would build the address of the start page for a page it cannot load
        return $this->pageUri->ofPage($site, $page, $language, [
            self::PARAMETER => $key,
            self::SIGNATURE => $this->signature($key),
        ]) ?? throw new UnexpectedValueException($error . ', as it does not show the page ' . $page, 1795776403);
    }

    /**
     * The component and the example a request asks for, if its signature is valid
     *
     * @param ServerRequestInterface $request Request
     *
     * @return array{string, string}|null Component and example; null if the request asks for none or is not signed
     */
    public function requested(ServerRequestInterface $request): ?array
    {
        $query = $request->getQueryParams();
        $key = $query[self::PARAMETER] ?? null;
        $signature = $query[self::SIGNATURE] ?? null;
        if (!\is_string($key) || !\is_string($signature) || !hash_equals($this->signature($key), $signature)) {
            return null;
        }

        $parts = explode('#', $key, 2);

        return 2 === \count($parts) ? [$parts[0], $parts[1]] : null;
    }

    /**
     * Signature of a component and an example
     *
     * @param string $key Component and example, such as "twc:event.teaser#cancelled"
     *
     * @return string
     */
    private function signature(string $key): string
    {
        return $this->hashes->hmac($key, self::class, HashAlgo::SHA3_256);
    }
}
