<?php

/**
 * Returns the absolute id of a node, or a reference to it
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

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Returns the absolute id of a node, or a reference to it
 *
 * @category  Tollwerk
 * @package   Tollwerk\TwBase
 * @author    Joschi Kuphal <joschi@tollwerk.de>
 * @copyright 2026 tollwerk GmbH
 * @license   https://spdx.org/licenses/GPL-2.0-or-later.html GPL-2.0-or-later
 * @link      https://code.tollwerk.net/tollwerk/typo3/extensions/tw-base
 */
final class IdrefViewHelper extends AbstractStructuredDataViewHelper
{
    /**
     * Register the arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('id', 'string', 'Id; without it, the address of the page', false);
        $this->registerArgument('global', 'bool', 'Whether the id is relative to the site, not the page', false, false);
        $this->registerArgument('object', 'bool', 'Whether to return a reference such as {"@id": "…"}', false, false);
    }

    /**
     * Return the id or the reference
     *
     * @return string|array{"@id": string}
     */
    public function render(): string|array
    {
        $graph = $this->graph();
        $id = $this->stringArgument('id');
        $idref = $this->boolArgument('global') ? $this->globalId($id) : null;
        $idref ??= ($id === null) ? $graph->baseUri() : $graph->normalizeId($id);

        return $this->boolArgument('object') ? ['@id' => $idref] : $idref;
    }

    /**
     * Id relative to the base of the site
     *
     * @param string|null $id Id, or NULL for the base of the site
     *
     * @return string|null Id, or NULL outside of a site
     */
    private function globalId(?string $id): ?string
    {
        $site = $this->request()?->getAttribute('site');
        if (!$site instanceof Site) {
            return null;
        }

        $base = (string) $site->getBase();

        return ($id === null) ? $base : rtrim($base, '/') . '/#' . ltrim($id, '#');
    }
}
